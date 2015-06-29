<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 12:23 PM
 */

namespace Persistency\Storage;

/**
 * Class RedisNotifListPersist
 * @package Persistency\Storage
 */
class RedisNotifListPersist extends AbstractStorage
{
    /**
     * @var NotifArchiveStorage
     */
    protected $archiveStorage;
    /**
     * @var RedisStorage
     */
    protected $storage;
    /**
     * @var array
     */
    protected $list;
    /**
     * @var int
     */
    protected $fireTime;

    public function __construct(RedisStorage $storage, $fireTime, NotifArchiveStorage $archiveStorage)
    {
        $this->storage        = $storage;
        $this->fireTime       = $fireTime;
        $this->archiveStorage = $archiveStorage;
    }

    /**
     * @return array
     */
    public function retrieve()
    {
        return $this->storage->getList($this->fireTime);
    }

    /**
     * @param array $payload
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->archiveStorage->append($payload);
        $this->storage->purgeList($this->fireTime);
    }
}
