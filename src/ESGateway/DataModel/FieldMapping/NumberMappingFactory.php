<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 12:24.
 */
namespace ESGateway\DataModel\FieldMapping;

use ESGateway\DataModel\FieldMapping\Number\ExactlyIndexedFloat;
use ESGateway\DataModel\FieldMapping\Number\ExactlyIndexedInteger;

/**
 * Class NumberMappingFactory.
 */
class NumberMappingFactory extends AbstractMappingFactory
{
    protected $listString = [
        'exactlyIndexedInteger' => [
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
        ],
        'exactlyIndexedFloat' => [
            'last_pay_amount',
            'history_pay_amount',
        ],
    ];

    /**
     * @return \Closure[]
     */
    protected function listHandlers()
    {
        return [
            function ($category, array $fieldList) {
                return $this->onCategory(
                    'exactlyIndexedInteger',
                    new ExactlyIndexedInteger(),
                    $category,
                    $fieldList
                );
            },
            function ($category, array $fieldList) {
                return $this->onCategory(
                    'exactlyIndexedFloat',
                    new ExactlyIndexedFloat(),
                    $category,
                    $fieldList
                );
            },
        ];
    }
}
