<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 11:53.
 */
namespace ESGateway\DataModel\FieldMapping;

use ESGateway\DataModel\FieldMapping\String\ExactlyIndexedIP;
use ESGateway\DataModel\FieldMapping\String\ExactlyIndexedString;
use ESGateway\DataModel\FieldMapping\String\FullTextIndexString;
use ESGateway\DataModel\FieldMapping\String\NoIndexedString;

/**
 * Class StringMappingFactory.
 */
class StringMappingFactory extends AbstractMappingFactory
{
    protected $listString = [
        'exactlyIndexed' => [
            'snsid',
            'name',
            'track_ref',
            'country',
            'email',
            'language',
        ],
        'fullTextIndexed' => [],
        'noIndexed' => [],
        'exactlyIndexedIP' => [
            'loginip',
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
                    'exactlyIndexed',
                    new ExactlyIndexedString(),
                    $category,
                    $fieldList
                );
            },
            function ($category, array $fieldList) {
                return $this->onCategory(
                    'noIndexed',
                    new NoIndexedString(),
                    $category,
                    $fieldList
                );
            },
            function ($category, array $fieldList) {
                return $this->onCategory(
                    'fullTextIndexed',
                    new FullTextIndexString(),
                    $category,
                    $fieldList
                );
            },
            function ($category, array $fieldList) {
                return $this->onCategory(
                    'exactlyIndexedIP',
                    new ExactlyIndexedIP(),
                    $category,
                    $fieldList
                );
            },
        ];
    }
}
