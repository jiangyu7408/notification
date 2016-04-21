<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 20:09.
 */
namespace unit\ES;

use Elasticsearch\Client;
use ESGateway\DataModel\FieldMapping\MappingFactory;
use ESGateway\Mapping;
use ESGateway\Type;

/**
 * Class MappingTest.
 */
class MappingTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareMapping()
    {
        $mapping = (new MappingFactory())->make();
        $fieldList = array_keys($mapping['properties']);

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
        $type = new Type('farm', 'user:test');
        $esClient = new Client(['hosts' => ['127.0.0.1']]);
        $instance = new Mapping($esClient, $type);
        $instance->deleteIndex();
        $ret = $instance->createIndex();
        dump($ret);
    }
}
