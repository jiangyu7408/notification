<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 11:49 AM
 */

namespace Persistency\Storage;

/**
 * Class RedisNotifPersist
 * @package Persistency\Storage
 */
class RedisNotifPersist extends AbstractStorage
{
    public function __construct(RedisStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        trigger_error('no retrieve action allowed');
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->storage->add($payload);
    }
}
