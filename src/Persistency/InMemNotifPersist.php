<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 5:50 PM
 */

namespace Persistency;

/**
 * Class InMemNotifPersist
 * @package Persistency
 */
class InMemNotifPersist implements IPersistency
{
    protected $payload;

    /**
     * @return array
     */
    public function retrieve()
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->payload = $payload;
    }
}
