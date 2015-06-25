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
        // TODO: fetch and return matched Notif objects
        return array(
            json_decode('{"appid":111,"snsid":"675097095878591","fireTime":1435215053,"feature":"feature2","trackRef":"feature2_8"}', true)
        );
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        // TODO: traverse whole payload to find out those notifications of changed state, and do persist job.
        error_log(__METHOD__ . ': ' . json_encode($payload));
    }
}
