<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:55 AM
 */

namespace Environment;

/**
 * Class Platform
 * @package Environment
 */
class Platform
{
    /**
     * @var string
     */
    protected $onlineSettingDir;
    /**
     * @var array
     */
    protected $mapping;

    public function __construct($entry)
    {
        $this->onlineSettingDir = $entry;
        $this->loadFacebookPlatforms();

        if (!defined('SYS_PATH')) {
            define('SYS_PATH', __DIR__);
        }
    }

    protected function loadFacebookPlatforms()
    {
        if (is_array($this->mapping)) {
            return;
        }

        if (!is_dir($this->onlineSettingDir)) {
            return;
        }

        $dirs = array_values(array_filter(scandir($this->onlineSettingDir), function ($dir) {
            if (strpos($dir, 'facebook') === 0) {
                return $dir;
            }
            return null;
        }));

        $this->mapping = [];
        foreach ($dirs as $dir) {
            $version                 = substr($dir, strrpos($dir, '_') + 1);
            $this->mapping[$version] = $this->onlineSettingDir . '/' . $dir;
        }
    }

    public function getMySQLShards($version)
    {
        /** @var array $all */
        $all = require $this->getPlatformSetting($version, 'database');
        assert(is_array($all));

        foreach ($all as $each) {
            if (!is_array($each)) {
                continue;
            }
            if (count($each) === 0) {
                continue;
            }

            yield $each;
        }
    }

    protected function getPlatformSetting($version, $name)
    {
        $filename = $this->mapping[$version] . '/' . $name . '.php';
        assert(is_file($filename) && is_readable($filename));

        return $filename;
    }
}
