<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 10:50.
 */
namespace ESGateway\DataModel\FieldMapping;

/**
 * Class AbstractFieldMapping.
 */
abstract class AbstractFieldMapping
{
    /** @var string */
    protected $dataType;
    /** @var string */
    protected $indexControl;
    /** @var string */
    protected $format;

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getIndexControl()
    {
        return $this->indexControl;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ret = [
            'type' => $this->dataType,
            'index' => $this->indexControl,
        ];

        if ($this->format) {
            $ret['format'] = $this->format;
        }

        return $ret;
    }
}
