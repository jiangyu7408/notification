<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:38 PM
 */
namespace ESGateway;

use Elasticsearch\Client;

class ElasticSearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    public function testDsnObject()
    {
        $ip     = '127.0.0.1';
        $port   = 9200;
        $dsnObj = $this->factory->makeDsnObject($ip, $port);
        static::assertInstanceOf(DSN::class, $dsnObj);

        $dsn = $this->factory->makeDsn($ip, $port);
        static::assertEquals($dsnObj->toString(), $dsn);
    }

    public function testMakeType()
    {
        $index    = 'test';
        $typeName = 'user:tw';

        $type = $this->factory->makeType($index, $typeName);
        static::assertInstanceOf(Type::class, $type);
        static::assertEquals($index, $type->index);
        static::assertEquals($typeName, $type->type);
    }

    public function testMakeClient()
    {
        $ip   = '127.0.0.1';
        $port = 9200;
        $dsn  = $this->factory->makeDsn($ip, $port);

        $client = $this->factory->makeClient($dsn);
        static::assertInstanceOf(Client::class, $client);
    }

    public function testMakeUser()
    {
        $json     = '{"uid":"19246","snsid":"825763410852946","email":"huitingsong1@126.com","level":"222","experience":"9285107","coins":"800172","reward_points":"10222","new_cash1":"0","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2015-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"1","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"Song Huiting","picture":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-xpt1\/v\/t1.0-1\/p50x50\/1381887_698181783611110_606257","loginnum":"2","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"1"}';
        $dbEntity = json_decode($json, true);
        static::assertArrayHasKey('snsid', $dbEntity);
        $userObj = $this->factory->makeUser($dbEntity);
        static::assertInstanceOf(User::class, $userObj);

        $userArr = $this->factory->toArray($userObj);
        static::assertArrayHasKey('snsid', $userArr);
    }

    protected function setup()
    {
        $this->factory = new Factory();
    }
}
