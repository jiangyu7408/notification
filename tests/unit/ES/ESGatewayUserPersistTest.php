<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 3:58 PM
 */
namespace Persistency\ElasticSearch;

use Elasticsearch\Client;
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
        $json = '{"uid":"88888","snsid":"825788888888888","email":"","level":"222","experience":"9285107","coins":"800172","reward_points":"10222","new_cash1":"0","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2015-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"1","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"","picture":"","loginnum":"2","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"1"}';
        return [[json_decode($json, true)]];
    }

    /**
     * @dataProvider userProvider
     * @param array $dbEntity
     */
    public function test($dbEntity)
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

        $this->client->shouldReceive('get')->times(1)->andReturn([
            'found'   => true,
            '_source' => $dbEntity
        ]);
        $found    = $this->persist->retrieve();
        $foundObj = $this->factory->makeUser($found);
        static::assertEquals($userArr, $this->factory->toArray($foundObj));
    }

    protected function setUp()
    {
        $this->factory = new Factory();

//        $ip   = '127.0.0.1';
//        $port = 9200;

//        $dsn          = $this->factory->makeDsn($ip, $port);
//        $this->client = $this->factory->makeClient($dsn);
        $this->client = Mockery::mock(Client::class);

        $index    = 'test';
        $typeName = 'user:tw';

        $this->type = $this->factory->makeType($index, $typeName);

        $this->persist = new GatewayUserPersist($this->client, $this->type);
    }
}
