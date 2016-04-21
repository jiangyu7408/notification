<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 11:08.
 */
namespace ESGateway\DataModel\FieldMapping\Number;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class ExactlyIndexedInteger.
 */
class ExactlyIndexedInteger extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'long';
    /** @var string */
    protected $indexControl = 'not_analyzed';
    /** @var string */
    protected $format = '';
}
