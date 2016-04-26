<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 11:40.
 */
namespace Facade\ElasticSearch;

use Elastica\Document;
use ESGateway\Factory;

/**
 * Class DocumentFactory.
 */
class DocumentFactory
{
    /**
     * DocumentFactory constructor.
     *
     * @param string $indexName
     * @param string $typeName
     */
    public function __construct($indexName, $typeName)
    {
        $this->indexName = $indexName;
        $this->typeName = $typeName;
        $this->userFactory = new Factory();
    }

    /**
     * @param string $snsid
     * @param array  $userInfo
     *
     * @return Document
     */
    public function make($snsid, array $userInfo)
    {
        assert(is_string($snsid) && strlen($snsid) > 0);
        if (array_key_exists('snsid', $userInfo)) {
            assert($userInfo['snsid'] === $snsid);
        }

        $user = $this->userFactory->makeUser($userInfo);
        $sanitizedUserInfo = $this->userFactory->toArray($user);

        $document = new Document($snsid, $sanitizedUserInfo);
        $document->setDocAsUpsert(true)
                 ->setIndex($this->indexName)
                 ->setType($this->typeName);

        return $document;
    }
}
