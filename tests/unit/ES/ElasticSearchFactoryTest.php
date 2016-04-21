<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:38 PM.
 */
namespace ESGateway;

use Elastica\Client;

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
    public function testDsnObject()
    {
        $host = '127.0.0.1';
        $port = 9200;
        $dsnObj = $this->factory->makeDsnObject($host, $port);
        static::assertInstanceOf(DSN::class, $dsnObj);

        $dsn = $this->factory->makeDsn($host, $port);
        static::assertEquals($dsnObj->toString(), $dsn);
    }

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
        $dsn = $this->factory->makeDsn($host, $port);

        $client = $this->factory->makeClient($dsn);
        static::assertInstanceOf(Client::class, $client);
    }

    /**
     *
     */
    public function testMakeUser()
    {
        require_once __DIR__.'/UserProvider.php';
        $jsonArr = \UserProvider::getJsonData();
        $json = array_shift($jsonArr);
        $dbEntity = json_decode($json, true);
        static::assertArrayHasKey('snsid', $dbEntity);
        $userObj = $this->factory->makeUser($dbEntity);
        static::assertInstanceOf(User::class, $userObj);

        $userArr = $this->factory->toArray($userObj);
        static::assertArrayHasKey('snsid', $userArr);

        $sortedArray = $userArr;
        ksort($sortedArray, SORT_STRING);
        static::assertSame($sortedArray, $userArr);
    }

    protected function setup()
    {
        $this->factory = new Factory();
    }
}
