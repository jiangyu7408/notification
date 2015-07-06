<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 5:00 PM
 */
namespace Repository;

use Elasticsearch\Client;
use ESGateway\Factory;
use ESGateway\Type;
use ESGateway\User;
use Persistency\ElasticSearch\GatewayUserPersist;

class ESGatewayUserRepoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ESGatewayUserRepo
     */
    protected $repo;
    /**
     * @var Factory
     */
    protected $factory;
    /**
     * @var Client
     */
    protected $client;
    /**
     * @var GatewayUserPersist
     */
    protected $persist;
    /**
     * @var Type
     */
    protected $type;

    public function testFire()
    {
        $userObjList = $this->userProvider();
        $userObj     = array_pop($userObjList);

        static::assertInstanceOf(User::class, $userObj);

        $this->repo->fire($userObj);

        $this->persist->setSnsid($userObj->snsid);
        $found    = $this->persist->retrieve();
        $foundObj = $this->factory->makeUser($found);
        static::assertEquals($this->factory->toArray($userObj), $this->factory->toArray($foundObj));
    }

    /**
     * @return array
     */
    public function userProvider()
    {
        $jsonArr = [
            '{"uid":"19246","snsid":"825763410852946","email":"huitingsong1@126.com","level":"222","experience":"9285107","coins":"800172","reward_points":"10222","new_cash1":"0","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2015-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"1","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"Song Huiting","picture":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-xpt1\/v\/t1.0-1\/p50x50\/1381887_698181783611110_606257","loginnum":"2","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"1"}',
            '{"uid":"6","snsid":"675097095878591","email":"test@126.com","level":"37","experience":"928507","coins":"1000","reward_points":"10222","new_cash1":"30","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2014-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"100","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"Song Huiting","picture":"","loginnum":"22","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"11"}'
        ];

        $users = [];
        foreach ($jsonArr as $json) {
            $dbEntity              = json_decode($json, true);
            $dbEntity['logintime'] = time();
            $users[]               = $this->factory->makeUser($dbEntity);
        }

        return $users;
    }

    public function testBulk()
    {
        $userObjList = $this->userProvider();
        $this->repo->burst($userObjList);

        foreach ($userObjList as $userObj) {
            $this->persist->setSnsid($userObj->snsid);
            $found    = $this->persist->retrieve();
            $foundObj = $this->factory->makeUser($found);
            static::assertEquals($this->factory->toArray($userObj), $this->factory->toArray($foundObj));
        }
    }

    protected function setUp()
    {
        $this->factory = new Factory();
        $ip            = '127.0.0.1';
        $port          = 9200;

        $dsn          = $this->factory->makeDsn($ip, $port);
        $this->client = $this->factory->makeClient($dsn);

        $index = 'farm';
        $typeName = 'user:tw';

        $this->type = $this->factory->makeType($index, $typeName);

        $this->persist = new GatewayUserPersist($this->client, $this->type);

        $this->repo = new ESGatewayUserRepo($this->persist, $this->factory);
        static::assertInstanceOf(ESGatewayUserRepo::class, $this->repo);
    }
}
