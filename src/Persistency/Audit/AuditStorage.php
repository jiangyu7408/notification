<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 5:26 PM
 */

namespace Persistency\Audit;

use Persistency\IPersistency;

/**
 * Class AuditStorage
 * @package Persistency\Audit
 */
class AuditStorage implements IPersistency
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @return array
     */
    public function retrieve()
    {
        return $this->items;
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
//        echo __METHOD__ . ': ' . print_r($payload, true) . PHP_EOL;
        $this->items[] = $payload;
    }
}
