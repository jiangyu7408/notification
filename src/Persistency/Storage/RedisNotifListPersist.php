<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 12:23 PM.
 */

namespace Persistency\Storage;

/**
 * Class RedisNotifListPersist.
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

    /**
     * @param RedisStorage        $storage
     * @param NotifArchiveStorage $archiveStorage
     */
    public function __construct(RedisStorage $storage, NotifArchiveStorage $archiveStorage)
    {
        $this->storage = $storage;
        $this->archiveStorage = $archiveStorage;
    }

    /**
     * @param $fireTime
     */
    public function setFireTime($fireTime)
    {
        $this->fireTime = $fireTime;
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
     *
     * @return bool
     */
    public function persist(array $payload)
    {
        $this->archiveStorage->append($this->fireTime, $payload);

        return $this->storage->purgeList($this->fireTime);
    }
}
