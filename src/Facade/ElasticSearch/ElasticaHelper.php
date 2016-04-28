<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 11:38.
 */
namespace Facade\ElasticSearch;

use Closure;
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
    }

    /**
     * @param boolean $verbose
     *
     * @return static
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;

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
                    $snsid = $data['snsid'];
                    $language = $data['language'];
                    dump($snsid.' => '.$language);
                },
                $documents
            );
        }

        try {
            $responseSet = $this->elastica->updateDocuments($documents);

            $snsidList = [];
            foreach ($responseSet as $response) {
                if (!$response->isOk()) {
                    $metaData = $response->getAction()->getMetadata();
                    $snsid = $metaData['_id'];
                    $snsidList[] = $snsid;
                }
            }
            if ($snsidList) {
                call_user_func($errorHandler, $snsidList);
            }
        } catch (\Exception $e) {
            error_log(print_r($e->getMessage(), true), 3, CONFIG_DIR.'/elastica.error');
            $this->elastica = $this->makeElastica();
        }
    }
}
