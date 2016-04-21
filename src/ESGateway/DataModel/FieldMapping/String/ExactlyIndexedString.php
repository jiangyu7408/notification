<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 10:54.
 */
namespace ESGateway\DataModel\FieldMapping\String;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class ExactlyIndexedString.
 */
class ExactlyIndexedString extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'string';
    /** @var string */
    protected $indexControl = 'not_analyzed';
    /** @var string */
    protected $format = '';
}
