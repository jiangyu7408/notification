<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:58 PM
 */

namespace Persistency;

/**
 * Class FireGun
 * @package Persistency
 */
class FireGun implements IPersistency
{
    public function __construct()
    {
        // @TODO: init connections with FB gateway
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        // no logical retrieve in this context.
        return array();
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        // TODO: send notification to FB gateway
    }
}
