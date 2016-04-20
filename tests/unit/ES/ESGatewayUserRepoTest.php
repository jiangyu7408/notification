<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 5:00 PM.
 */
namespace Repository;

use Elasticsearch\Client;
use ESGateway\Factory;
use ESGateway\Type;
use ESGateway\User;
use Mockery;
use Persistency\ElasticSearch\GatewayUserPersist;

/**
 * Class ESGatewayUserRepoTest.
 */
class ESGatewayUserRepoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ESGatewayUserRepo
     */
    protected $userRepo;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var Mockery\MockInterface
     */
    protected $esClient;
    /**
     * @var GatewayUserPersist
     */
    protected $userPersist;
    /**
     * @var Type
     */
    protected $type;

    /**
     */
    public function testFire()
    {
        $userObjList = $this->userProvider();
        $userObj = array_pop($userObjList);

        static::assertInstanceOf(User::class, $userObj);

        $this->esClient->shouldReceive('bulk')->times(1)->andReturn(true);
        $this->userRepo->fire($userObj);

        $this->userPersist->setSnsid($userObj->snsid);
        $this->esClient->shouldReceive('get')
                       ->times(1)
                       ->andReturn(
                           [
                               'found' => true,
                               '_source' => $this->factory->toArray($userObj),
                           ]
                       );
        $found = $this->userPersist->retrieve();
        $foundObj = $this->factory->makeUser($found);
        static::assertEquals($this->factory->toArray($userObj), $this->factory->toArray($foundObj));
    }

    /**
     * @return array
     */
    public function userProvider()
    {
        require_once __DIR__.'/UserProvider.php';

        return \UserProvider::listUser();
    }

    public function testBulk()
    {
        $userObjList = $this->userProvider();
        $this->esClient->shouldReceive('bulk')->times(1)->andReturn(true);
        $this->userRepo->burst($userObjList);

        foreach ($userObjList as $userObj) {
            $this->userPersist->setSnsid($userObj->snsid);
            $this->esClient->shouldReceive('get')
                           ->times(1)
                           ->andReturn(
                               [
                                   'found' => true,
                                   '_source' => $this->factory->toArray($userObj),
                               ]
                           );
            $found = $this->userPersist->retrieve();
            $foundObj = $this->factory->makeUser($found);
            static::assertEquals(
                $this->factory->toArray($userObj),
                $this->factory->toArray($foundObj)
            );
        }
    }

    protected function setUp()
    {
        $this->factory = new Factory();

        $index = 'farm';
        $typeName = 'user:tw';

        /* @var Client $esClient */
        $this->esClient = $esClient = Mockery::mock(Client::class);
        $this->type = $this->factory->makeType($index, $typeName);

        $this->userPersist = new GatewayUserPersist($esClient, $this->type);

        $this->userRepo = new ESGatewayUserRepo($this->userPersist, $this->factory);
        static::assertInstanceOf(ESGatewayUserRepo::class, $this->userRepo);
    }
}
