<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 11:09.
 */
namespace ESGateway\DataModel\FieldMapping\Number;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class ExactlyIndexedFloat.
 */
class ExactlyIndexedFloat extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'double';
    /** @var string */
    protected $indexControl = 'not_analyzed';
    /** @var string */
    protected $format = '';
}
