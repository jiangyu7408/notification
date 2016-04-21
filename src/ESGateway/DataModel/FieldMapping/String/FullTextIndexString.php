<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 10:52.
 */
namespace ESGateway\DataModel\FieldMapping\String;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class FullTextIndexString.
 */
class FullTextIndexString extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'string';
    /** @var string */
    protected $indexControl = 'analyzed';
    /** @var string */
    protected $format = '';
}
