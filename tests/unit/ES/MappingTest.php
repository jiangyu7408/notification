<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 20:09.
 */
namespace unit\ES;

use Elastica\Bulk\Action\UpdateDocument;
use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Request;
use Elastica\Search;
use Elastica\Type\Mapping;
use ESGateway\DataModel\FieldMapping\MappingFactory;
use ESGateway\Factory;
use ESGateway\User;

/**
 * Class MappingTest.
 */
class MappingTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareMapping()
    {
        $mapping = (new MappingFactory())->make();
        $fieldList = array_keys($mapping);

        $expectedFieldList = [
            'snsid',
            'name',
            'track_ref',
            'country',
            'email',
            'language',
            'loginip',
            'addtime',
            'logintime',
            'last_pay_time',
            'uid',
            'coins',
            'continuous_day',
            'experience',
            'gas',
            'greenery',
            'level',
            'loginnum',
            'new_cash1',
            'new_cash2',
            'new_cash3',
            'op',
            'pay_times',
            'reward_points',
            'sign_points',
            'size_x',
            'status',
            'top_map_size',
            'water_exp',
            'water_level',
            'chef_level',
            'silver_coins',
            'reputation',
            'reputation_level',
            'restaurant_level',
            'guild_level',
            'adventure_point',
            'blue_crystal',
            'purple_crystal',
            'golden_crystal',
            'vip_level',
            'vip_points',
            'last_pay_amount',
            'history_pay_amount',
        ];

        sort($fieldList, SORT_STRING);
        sort($expectedFieldList, SORT_STRING);
        static::assertEquals($expectedFieldList, $fieldList);
    }

    public function testCreateAndDelete()
    {
        $this->deleteIndex();
        $this->createIndex();
        $expectedMapping = $this->createMapping();
        $expectedFieldList = array_keys($expectedMapping);
        sort($expectedFieldList, SORT_STRING);

        $mapping = $this->getMapping();
        $fieldList = array_keys($mapping);
        sort($fieldList, SORT_STRING);

        $this->assertEquals($expectedFieldList, $fieldList);

        $this->addAlias();

        $this->addDocuments();
        $this->updateDocuments();

        $this->getClient()->getIndex($this->getIndexName())->refresh();

        $this->getDocuments();
//        $this->searchDocuments();
        $this->deleteDocuments();
    }

    public function addAlias()
    {
        dump(__METHOD__);
        $client = $this->getClient();
        $index = $client->getIndex($this->getIndexName());
        $response = $index->addAlias('farm');
        $this->assertTrue($response->isOk());
    }

    protected function searchDocuments()
    {
        dump(__METHOD__);
        $client = $this->getClient();
        $index = $client->getIndex($this->getIndexName());
        $type = $index->getType($this->getTypeName());

        if (true) {
            $query = json_decode('{"query":{"bool":{"must":[{"term":{"uid":19246}},{"term":{"name":"XXXXXXXXXX"}},{"term":{"op":30}}],"filter":[{"range":{"level":{"from":10,"to":300}}},{"range":{"addtime":{"gte":"20150706T145200+0800","lte":"20150707T145203+0800"}}}]}}}', true);
            dump($query);

            $path = $index->getName().'/'.$type->getName().'/_search';
            $response = $client->request($path, Request::GET, $query);
            $this->assertTrue($response->isOk());
//            dump($response->getEngineTime());
//            dump($response->getQueryTime());
//            dump($response->getShardsStatistics());
//            dump($response->getStatus()); // http status code
//            dump($response->getTransferInfo());
            dump($response->getData()['hits']['hits']);
        }

        if (false) {
            $search = new Search($client);
            $search->addIndex($index)
                   ->addType($type);
            $query = new Query\BoolQuery();
//        $query->setFrom(0);
//        $query->setSize(10);
//        $query->setSort(['uid' => 'asc']);
//        $query->setFields(['snsid', 'uid']);
//        $query->setHighlight(['fields' => 'uid']);
//        $query->setExplain(true);
//        $term = new Query\Term(['name' => 'XXXXXXXXXX']);
//        $query->setQuery($term);

            $query->addMust(new Term(['uid' => 19246]));
            $query->addMust(new Term(['name' => 'XXXXXXXXXX']));
            $query->addMust(new Term(['op' => 30]));
//        $query->addMustNot(new Term(['country' => 'CN']));

            $range = new Query\Range('level', ['from' => 10, 'to' => 300]);
            $query->addFilter($range);

            $range = new Query\Range();
            $range->addField(
                'addtime',
                [
                    'gte' => '20150706T145200+0800',
                    'lte' => '20150707T145203+0800',
                ]
            );
            $query->addFilter($range);

            $search->setQuery($query);
            $resultSet = $search->search();
            dump('Hit: '.$resultSet->count());
            $queryArray = $resultSet->getQuery()->toArray();
            dump(json_encode($queryArray));
            dump($resultSet->getResponse()->getData()['hits']['hits']);
            dump('query time: '.\PHP_Timer::secondsToTimeString($resultSet->getResponse()->getQueryTime()));
        }
    }

    protected function updateDocuments()
    {
        dump(__METHOD__);
        require_once __DIR__.'/UserProvider.php';
        $userList = (new \UserProvider())->listUser();

        $documents = array_map(
            function (User $user) {
                $document = new Document($user->snsid, ['name' => str_repeat('X', 10)]);
                $document->setDocAsUpsert(true)
                         ->setIndex($this->getIndexName())
                         ->setType($this->getTypeName());

                return $document;
            },
            $userList
        );

        $responseSet = $this->getClient()
                            ->getIndex($this->getIndexName())
                            ->updateDocuments($documents);
        foreach ($responseSet as $response) {
            $data = $response->getData();
            $this->assertEquals(2, $data['_version']);
            $updateDocument = $response->getAction();
            $this->assertInstanceOf(UpdateDocument::class, $updateDocument);
        }
    }

    protected function deleteDocuments()
    {
        dump(__METHOD__);
        require_once __DIR__.'/UserProvider.php';
        $userList = (new \UserProvider())->listUser();

        $documents = array_map(
            function (User $user) {
                $document = new Document($user->snsid);
                $document->setIndex($this->getIndexName())
                         ->setType($this->getTypeName());

                return $document;
            },
            $userList
        );

        $responseSet = $this->getClient()
                            ->getIndex($this->getIndexName())
                            ->deleteDocuments($documents);
        foreach ($responseSet as $response) {
            $data = $response->getData();
            $this->assertEquals(3, $data['_version']);
            $action = $response->getAction();
            $this->assertEquals('delete', $action->getOpType());
        }
    }

    protected function addDocuments()
    {
        dump(__METHOD__);
        require_once __DIR__.'/UserProvider.php';
        $userList = (new \UserProvider())->listUser();

        $documents = array_map(
            function (User $user) {
                return $this->makeDocument($user);
            },
            $userList
        );

        $responseSet = $this->getClient()
                            ->getIndex($this->getIndexName())
                            ->addDocuments($documents);
        foreach ($responseSet as $response) {
            $data = $response->getData();
            $this->assertEquals(1, $data['_version']);
            $source = $response->getAction()
                               ->getSource();
            $this->assertArrayHasKey('snsid', $source);
            $this->assertArrayHasKey('uid', $source);
        }
    }

    protected function getDocuments()
    {
        dump(__METHOD__);
        require_once __DIR__.'/UserProvider.php';
        $userList = (new \UserProvider())->listUser();

        $snsidList = array_map(
            function (User $user) {
                return $user->snsid;
            },
            $userList
        );

        array_map(
            function ($snsid) {
                $document = $this->getClient()
                                 ->getIndex($this->getIndexName())
                                 ->getType($this->getTypeName())
                                 ->getDocument($snsid);
                $this->assertInstanceOf(Document::class, $document);
                $data = $document->getData();
                $this->assertEquals($snsid, $data['snsid']);
                $params = $document->getParams();
                $this->assertArrayHasKey('_version', $params);
            },
            $snsidList
        );
    }

    /**
     * @param User $user
     *
     * @return Document
     */
    protected function makeDocument(User $user)
    {
        $document = new Document($user->snsid, (new Factory())->toArray($user));
        $document->setDocAsUpsert(true)
                 ->setIndex($this->getIndexName())
                 ->setType($this->getTypeName());

        return $document;
    }

    protected function getMapping()
    {
        dump(__METHOD__);
        $response = $this->getClient()
                         ->getIndex($this->getIndexName())
                         ->getType($this->getTypeName())
                         ->getMapping();

        $this->assertTrue(is_array($response) && isset($response[$this->getTypeName()]));

        return $response[$this->getTypeName()]['properties'];
    }

    protected function createMapping()
    {
        dump(__METHOD__);
        $type = $this->getClient()
                     ->getIndex($this->getIndexName())
                     ->getType($this->getTypeName());
        $mapping = new Mapping();
        $mapping->setType($type);

        $mappingProperties = (new MappingFactory())->make();
        $mapping->setProperties($mappingProperties);
        $response = $mapping->send();
//        dump($response);
        $this->assertNotTrue($response->hasError());

        return $mappingProperties;
    }

    protected function createIndex()
    {
        /** @var \Elastica\Response $response */
        $response = $this->getClient()
                         ->getIndex($this->getIndexName())
                         ->create();
        dump(__METHOD__);
//        dump($response);
        $this->assertNotTrue($response->hasError());
    }

    protected function deleteIndex()
    {
        dump(__METHOD__);
        $exist = $this->getClient()
                      ->getIndex($this->getIndexName())
                      ->exists();
        if ($exist) {
            $response = $this->getClient()
                             ->getIndex($this->getIndexName())
                             ->delete();
//            dump($response);
            $this->assertNotTrue($response->hasError());
        }
    }

    protected function getClient()
    {
        return new Client();
    }

    protected function getIndexName()
    {
        return 'farm_v2';
    }

    protected function getTypeName()
    {
        static $name;
        if ($name === null) {
            //            $name = 'user:test:v'.time();
            $name = 'user:test:v1461226876';
//            dump($name);
        }

        return $name;
    }
}
