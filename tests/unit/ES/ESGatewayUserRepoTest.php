<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 5:00 PM.
 */
namespace Repository;

use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
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

        $esIndex = Mockery::mock(Index::class);
        $esIndex->shouldReceive('addDocuments')
                ->times(1)
                ->andReturn(Mockery::mock(ResponseSet::class));
        $this->esClient->shouldReceive('getIndex')
                       ->times(1)
                       ->andReturn($esIndex);
        $this->userRepo->fire($userObj, $errorInfo);

        $this->userPersist->setSnsid($userObj->snsid);

        $esIndex = Mockery::mock(Index::class);
        $esType = Mockery::mock(\Elastica\Type::class);

        $document = Mockery::mock(Document::class);
        $document->shouldReceive('getData')
                 ->times(1)
                 ->andReturn((new Factory())->toArray($userObj));

        $esType->shouldReceive('getDocument')
               ->times(1)
               ->andReturn($document);
        $esIndex->shouldReceive('getType')
                ->times(1)
                ->andReturn($esType);

        $this->esClient->shouldReceive('getIndex')
                       ->times(1)
                       ->andReturn($esIndex);

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
