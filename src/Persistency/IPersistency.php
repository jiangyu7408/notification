<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:47 PM
 */

namespace Persistency;

/**
 * Class IPersistency
 * @package Persistency
 */
interface IPersistency
{
    /**
     * @return array
     */
    public function retrieve();

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload);
}
