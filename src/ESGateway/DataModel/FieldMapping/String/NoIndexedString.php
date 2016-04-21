<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 10:55.
 */
namespace ESGateway\DataModel\FieldMapping\String;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class NoIndexedString.
 */
class NoIndexedString extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'string';
    /** @var string */
    protected $indexControl = 'no';
    /** @var string */
    protected $format = '';
}
