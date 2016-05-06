<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:38 PM.
 */
namespace unit\ES;

use Elastica\Client;
use ESGateway\Factory;
use ESGateway\Type;
use ESGateway\User;

/**
 * Class ElasticSearchFactoryTest.
 */
class ElasticSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     *
     */
    public function testMakeType()
    {
        $index = 'test';
        $typeName = 'user:tw';

        $type = $this->factory->makeType($index, $typeName);
        static::assertInstanceOf(Type::class, $type);
        static::assertEquals($index, $type->index);
        static::assertEquals($typeName, $type->type);
    }

    /**
     *
     */
    public function testMakeClient()
    {
        $host = '127.0.0.1';
        $port = 9200;
        $client = $this->factory->makeClient($host, $port);
        static::assertInstanceOf(Client::class, $client);
    }

    /**
     *
     */
    public function testMakeUser()
    {
        require_once __DIR__.'/UserProvider.php';
        $jsonArr = \UserProvider::getJsonData();

        foreach ($jsonArr as $json) {
            $dbEntity = json_decode($json, true);
            static::assertArrayHasKey('snsid', $dbEntity);
            $userObj = $this->factory->makeUser($dbEntity);
            static::assertInstanceOf(User::class, $userObj);

            $userArr = $this->factory->toArray($userObj);
            static::assertArrayHasKey('snsid', $userArr);
            static::assertArrayHasKey('status', $userArr, print_r($userArr, true));
        }
    }

    protected function setup()
    {
        $this->factory = new Factory();
    }
}
