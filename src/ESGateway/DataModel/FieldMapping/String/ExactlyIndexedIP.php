<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 11:02.
 */
namespace ESGateway\DataModel\FieldMapping\String;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class ExactlyIndexedIP.
 */
class ExactlyIndexedIP extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'ip';
    /** @var string */
    protected $indexControl = 'not_analyzed';
    /** @var string */
    protected $format = '';
}
