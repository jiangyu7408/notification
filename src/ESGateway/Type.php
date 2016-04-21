<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 2:25 PM.
 */

namespace ESGateway;

/**
 * Class Type.
 */
class Type
{
    /**
     * @var string
     */
    public $index;
    /**
     * @var string
     */
    public $type;

    /**
     * Type constructor.
     *
     * @param string $index
     * @param string $type
     */
    public function __construct($index, $type)
    {
        $this->index = $index;
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return sprintf('index: %s, type: %s', $this->index, $this->type);
    }
}
