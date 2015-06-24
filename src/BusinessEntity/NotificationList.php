<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:30 PM
 */

namespace BusinessEntity;

/**
 * Class NotificationList
 * @package BusinessEntity
 */
class NotificationList implements \ArrayAccess
{
    /**
     * @var Notification[]
     */
    protected $list = array();

    public function __construct(array $notifications)
    {
        $this->list = $notifications;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->list);
    }

    public function offsetGet($offset)
    {
        return $this->list[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->list[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->list[$offset]);
    }
}
