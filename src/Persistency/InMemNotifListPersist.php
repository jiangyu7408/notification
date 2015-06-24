<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:35 PM
 */

namespace Persistency;

/**
 * Class InMemNotifListPersist
 * @package Persistency
 */
class InMemNotifListPersist implements IPersistency
{
    /**
     * @return array
     */
    public function retrieve()
    {
        // TODO: fetch and return matched Notification objects
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        // TODO: traverse whole payload to find out those notifications of changed state, and do persist job.
    }
}
