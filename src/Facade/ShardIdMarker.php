<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/25
 * Time: 17:52.
 */
namespace Facade;

/**
 * Class ShardIdMarker.
 */
class ShardIdMarker
{
    /** @var string */
    protected $filePath;

    /**
     * CalendarDayMarker constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->validateFilePath($filePath);
        $this->filePath = $filePath;
    }

    /**
     * @param string $shardId
     */
    public function mark($shardId)
    {
        if ($this->isMarked($shardId)) {
            return;
        }

        file_put_contents($this->makeMarker($shardId), time());
    }

    /**
     * @param string $shardId
     *
     * @return bool
     */
    public function isMarked($shardId)
    {
        return file_exists($this->makeMarker($shardId));
    }

    /**
     * @param string $filePath
     */
    protected function validateFilePath($filePath)
    {
        if (strpos($filePath, '/mnt/htdocs/notif') !== 0) {
            throw new \InvalidArgumentException('file path location invalid');
        }
        if (is_dir($filePath) && !is_writable($filePath)) {
            throw new \InvalidArgumentException($filePath.' not writable');
        }
        if (!is_dir($filePath)) {
            $success = mkdir($filePath, 0755, true);
            if (!$success) {
                throw new \InvalidArgumentException(json_encode(error_get_last()));
            }
        }
    }

    /**
     * @param string $shardId
     *
     * @return string
     */
    protected function makeMarker($shardId)
    {
        return sprintf('%s/%s', $this->filePath, $shardId);
    }
}
