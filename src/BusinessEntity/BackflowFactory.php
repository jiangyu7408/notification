<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 3:34 PM.
 */

namespace BusinessEntity;

/**
 * Class BackflowFactory.
 */
class BackflowFactory
{
    /**
     * @param array $input
     *
     * @return Backflow
     */
    public function make(array $input)
    {
        $backflow = new Backflow();

        $keys = array_keys(get_object_vars($backflow));
        array_map(function ($key) use ($backflow, $input) {
            $backflow->$key = $input[$key];
        }, $keys);

        return $backflow;
    }

    /**
     * @param Backflow $backflow
     *
     * @return array
     */
    public function toArray(Backflow $backflow)
    {
        return get_object_vars($backflow);
    }
}
