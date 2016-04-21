<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 10:56.
 */
namespace ESGateway\DataModel\FieldMapping\Date;

use ESGateway\DataModel\FieldMapping\AbstractFieldMapping;

/**
 * Class FullIndexedBasicDate.
 */
class FullIndexedBasicDate extends AbstractFieldMapping
{
    /** @var string */
    protected $dataType = 'date';
    /** @var string */
    protected $indexControl = 'analyzed';
    /** @var string */
    protected $format = 'basic_date_time_no_millis||epoch_second';
}
