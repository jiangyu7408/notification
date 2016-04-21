<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 3:58 PM.
 */
namespace Persistency\ElasticSearch;

use Elastica\Client;
use Elastica\Index;
use ESGateway\Factory;
use ESGateway\Type;
use ESGateway\User;
use Mockery;

class ESGatewayUserPersistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $factory;
    protected $client;
    /**
     * @var GatewayUserPersist
     */
    protected $persist;
    /**
     * @var Type
     */
    protected $type;

    /**
     * @return array
     */
    public function userProvider()
    {
        require_once __DIR__.'/UserProvider.php';
        $jsonArr = \UserProvider::getJsonData();

        $data = array_map(
            function ($json) {
                return [json_decode($json, true)];
            },
            $jsonArr
        );

        return $data;
    }

    /**
     * @dataProvider userProvider
     *
     * @param array $dbEntity
     */
    public function test(array $dbEntity)
    {
        static::assertInstanceOf(GatewayUserPersist::class, $this->persist);

        $dbEntity['logintime'] = time();

        static::assertArrayHasKey('snsid', $dbEntity);
        $userObj = $this->factory->makeUser($dbEntity);
        static::assertInstanceOf(User::class, $userObj);
        $userArr = $this->factory->toArray($userObj);
        static::assertArrayHasKey('snsid', $userArr);

        $this->client->shouldReceive('bulk')->times(1)->andReturn(true);
        $success = $this->persist->persist([$userArr]);
        static::assertTrue($success);

        $this->persist->setSnsid($userObj->snsid);

        $this->client->shouldReceive('get')
                     ->times(1)
                     ->andReturn(
                         [
                             'found' => true,
                             '_source' => $dbEntity,
                         ]
                     );

        $esIndex = Mockery::mock(Index::class);
        $esType = Mockery::mock(\Elastica\Type::class);
        $esType->shouldReceive('getDocument')
               ->times(1)
               ->andReturn($dbEntity);
        $esIndex->shouldReceive('getType')
                ->times(1)
                ->andReturn($esType);

        $this->client->shouldReceive('getIndex')
                     ->times(1)
                     ->andReturn($esIndex);
        $found = $this->persist->retrieve();
        dump($found);
        $foundObj = $this->factory->makeUser($found);
        static::assertEquals($userArr, $this->factory->toArray($foundObj));
    }

    protected function setUp()
    {
        $this->factory = new Factory();
        /** @var Client $client */
        $this->client = $client = Mockery::mock(Client::class);

        $index = 'test';
        $typeName = 'user:tw';

        $this->type = $this->factory->makeType($index, $typeName);

        $this->persist = new GatewayUserPersist($client, $this->type);
    }
}
