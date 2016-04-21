<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 12:21.
 */
namespace ESGateway\DataModel\FieldMapping;

use ESGateway\DataModel\FieldMapping\Date\FullIndexedBasicDate;

/**
 * Class DateMappingFactory.
 */
class DateMappingFactory extends AbstractMappingFactory
{
    protected $listString = [
        'fullTextIndexed' => [
            'addtime',
            'logintime',
            'last_pay_time',
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
                    'fullTextIndexed',
                    new FullIndexedBasicDate(),
                    $category,
                    $fieldList
                );
            },
        ];
    }
}
