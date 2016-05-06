<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 11:38.
 */
namespace Facade\ElasticSearch;

use Closure;
use Elastica\Bulk\Response;
use Elastica\Client;
use Elastica\Document;
use Generator;

/**
 * Class ElasticaHelper.
 */
class ElasticaHelper
{
    /** @var DocumentFactory */
    protected $documentFactory;
    /** @var Client */
    protected $elastica;
    /** @var int */
    protected $magicNumber;
    /** @var bool */
    protected $verbose = false;
    /** @var Closure */
    protected $versionHandler;
    /** @var Closure */
    protected $errorHandler;

    /**
     * ElasticaHelper constructor.
     *
     * @param string $gameVersion
     * @param string $indexName
     * @param int    $magicNumber
     */
    public function __construct($gameVersion, $indexName, $magicNumber = 500)
    {
        $docPrototype = new Document();
        $docPrototype->setIndex($indexName)->setType('user:'.$gameVersion);
        $this->documentFactory = new DocumentFactory($docPrototype);
        $this->elastica = $this->makeElastica();
        $this->magicNumber = $magicNumber;

        $this->errorHandler = function (\Exception $exception) {
            error_log(print_r($exception->getMessage(), true), 3, CONFIG_DIR.'/elastica.error');
        };
    }

    /**
     * @param boolean $verbose
     *
     * @return static
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
        if ($this->verbose) {
            unlink(CONFIG_DIR.'/elastica.update.version');
            $this->versionHandler = function (array $versionList) {
                error_log(print_r($versionList, true), 3, CONFIG_DIR.'/elastica.update.version');
            };
        }

        return $this;
    }

    /**
     * @param array   $userInfoList
     * @param Closure $errorHandler
     */
    public function update(array $userInfoList, Closure $errorHandler)
    {
        $documentsGenerator = $this->documentsGenerator($userInfoList);
        foreach ($documentsGenerator as $documents) {
            $this->batchUpdate($documents, $errorHandler);
        }
    }

    /**
     * @return Client
     */
    protected function makeElastica()
    {
        return new Client(
            [
                'host' => ELASTIC_SEARCH_HOST,
                'port' => ELASTIC_SEARCH_PORT,
            ]
        );
    }

    /**
     * @param array $userInfoList
     *
     * @return Generator
     */
    protected function documentsGenerator(array $userInfoList)
    {
        while (($batch = array_splice($userInfoList, 0, $this->magicNumber))) {
            $documents = array_map(
                function (array $userInfo) {
                    return $this->documentFactory->make($userInfo['snsid'], $userInfo);
                },
                $batch
            );
            yield $documents;
        }
    }

    /**
     * @param Document[] $documents
     * @param Closure    $errorHandler
     */
    protected function batchUpdate(array $documents, Closure $errorHandler)
    {
        $count = count($documents);
        if ($count === 0) {
            return;
        }

        if ($this->verbose) {
            array_map(
                function (Document $document) {
                    $data = $document->getData();
                    dump($data);
                },
                $documents
            );
        }

        $this->validateFields($documents);

        try {
            $responseSet = $this->elastica->updateDocuments($documents);

            $failedSnsidList = [];
            $versionList = [];
            foreach ($responseSet as $response) {
                $this->parseResponse($response, $failedSnsidList, $versionList);
            }
            if ($failedSnsidList) {
                call_user_func($errorHandler, $failedSnsidList);
            }
            if (is_callable($this->versionHandler)) {
                call_user_func($this->versionHandler, $versionList);
            }
        } catch (\Exception $exception) {
            if (is_callable($this->errorHandler)) {
                call_user_func($this->errorHandler, $exception);
            }
            $this->elastica = $this->makeElastica(); // Try to keep going on updating
        }
    }

    /**
     * @param Response $response
     * @param array    $failedSnsidList
     * @param array    $versionList
     *
     * @return array
     */
    protected function parseResponse(Response $response, array &$failedSnsidList, array &$versionList)
    {
        $action = $response->getAction();
        if (!$response->isOk()) {
            $metaData = $action->getMetadata();
            $snsid = $metaData['_id'];
            $failedSnsidList[] = $snsid;

            return;
        }

        $responseData = $response->getData();
        $snsid = $responseData['_id'];
        $version = $responseData['_version'];
        $versionList[$snsid] = ['doc' => $action->getSource(), 'version' => $version];
    }

    /**
     * @param Document[] $documents
     */
    private function validateFields(array $documents)
    {
        $elementaryFieldList = ['country', 'locale', 'snsid', 'uid'];
        array_map(
            function (Document $document) use ($elementaryFieldList) {
                $data = $document->getData();
                foreach ($elementaryFieldList as $elementaryField) {
                    assert(
                        array_key_exists($elementaryField, $data),
                        'No '.$elementaryField.' field: '.print_r($data, true)
                    );
                }
            },
            $documents
        );
    }
}
