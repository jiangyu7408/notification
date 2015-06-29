<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/29
 * Time: 12:26 PM
 */

namespace Persistency\Storage;

/**
 * Class NotifArchiveStorage
 * @package Persistency\Storage
 */
class NotifArchiveStorage
{
    /**
     * @param int $fireTime
     * @return string
     */
    private function mapToFile($fireTime)
    {
        if (date_default_timezone_get() === '') {
            date_default_timezone_set('PRC');
        }

        $baseDir  = '/tmp/notif.archive/';
        $filename = $baseDir . date('Ymd/H/i/s', $fireTime);
        $dir      = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $filename;
    }

    /**
     * @param $fireTime
     * @param array $list
     */
    public function append($fireTime, array $list)
    {
        $filename = $this->mapToFile($fireTime);
        $handle   = fopen($filename, 'w');

        foreach ($list as $each) {
            fwrite($handle, json_encode($each));
        }

        fclose($handle);
    }

    /**
     * @param $fireTime
     * @return array
     */
    public function find($fireTime)
    {
        $filename = $this->mapToFile($fireTime);

        $result = [];

        $handle = fopen($filename, 'r');
        if (!is_resource($handle)) {
            return $result;
        }

        while (($line = fgets($handle))) {
            $result[] = json_decode($line, true);
        }

        return $result;
    }
}
