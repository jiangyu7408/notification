<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 13:27.
 */
use ESGateway\Factory;

/**
 * Class UserProvider.
 */
class UserProvider
{
    /**
     * @return array
     */
    public static function getJsonData()
    {
        return [
            '{"uid":"19246","snsid":"825763410852946","email":"huitingsong1@126.com","level":"222","experience":"9285107","coins":"800172","reward_points":"10222","new_cash1":"0","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2015-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"1","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"Song Huiting","picture":"https:\/\/fbcdn-profile-a.akamaihd.net\/hprofile-ak-xpt1\/v\/t1.0-1\/p50x50\/1381887_698181783611110_606257","loginnum":"2","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"1","history_pay_amount":"100","last_pay_time":"1436165599","last_pay_amount":"34"}',
            '{"uid":"6","snsid":"675097095878591","email":"test@126.com","level":"37","experience":"928507","coins":"1000","reward_points":"10222","new_cash1":"30","new_cash2":"0","new_cash3":"0","order_points":"0","time_points":"0","op":"30","gas":"0","lottery_coins":"0","size_x":"60","size_y":"60","top_map_size":"0","max_work_area_size":"1","work_area_size":"1","addtime":"2014-07-06 14:52:04","logintime":"1436165599","loginip":"10.0.16.76","status":"1","continuous_day":"100","point":"0","feed_status":"0","extra_info":"","track_ref":"","fish_op":"0","name":"Song Huiting","picture":"","loginnum":"22","note":null,"water_exp":"0","greenery":"0","pay_times":"0","water_level":"1","fb_source":"","sign_points":"0","fb_source_last":"","track_ref_last":"","silver_coins":"0","reputation":"0","reputation_level":"11","history_pay_amount":"100","last_pay_time":"1436165599","last_pay_amount":"34"}',
        ];
    }

    /**
     * @return array
     */
    public static function listUser()
    {
        $jsonArr = self::getJsonData();
        $factory = new Factory();
        $users = [];
        foreach ($jsonArr as $json) {
            $dbEntity = json_decode($json, true);
            $dbEntity['logintime'] = time();
            $dbEntity['last_pay_time'] = time();
            $dbEntity['last_pay_amount'] = rand(10, 100);
            $dbEntity['history_pay_amount'] = rand(100, 1000);
            $users[] = $factory->makeUser($dbEntity);
        }

        return $users;
    }
}
