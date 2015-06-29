<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:50 PM
 */

namespace Persistency\Storage;

/**
 * Class InMemNotifPersist
 * @package Persistency
 */
class InMemNotifPersist extends AbstractStorage
{
    protected $payload;

    /**
     * @return array
     */
    public function retrieve()
    {
        // no calling on this method is a supposed manner.
        return [];
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->payload = $payload;
        echo __METHOD__ . ': ' . json_encode($payload);
    }
}
