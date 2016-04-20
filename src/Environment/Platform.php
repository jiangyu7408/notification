<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 11:55 AM.
 */
namespace Environment;

/**
 * Class Platform.
 */
class Platform
{
    /** @var string */
    protected $onlineSettingDir;
    /** @var string */
    protected $gameVersion;
    /** @var array */
    protected $mapping;

    /**
     * Platform constructor.
     *
     * @param string $entry
     * @param string $gameVersion
     */
    protected function __construct($entry, $gameVersion)
    {
        $this->onlineSettingDir = $entry;
        $this->gameVersion = $gameVersion;

        $this->loadFacebookPlatforms();

        if (!defined('SYS_PATH')) {
            define('SYS_PATH', __DIR__);
        }
    }

    /**
     * @return \Generator
     */
    public function getMySQLShards()
    {
        /** @var array $all */
        $all = require $this->getPlatformSetting($this->gameVersion, 'database');
        assert(is_array($all));

        foreach ($all as $shardId => $each) {
            if (!is_array($each)) {
                continue;
            }
            if (count($each) === 0) {
                continue;
            }
            $each['shardId'] = $shardId;

            yield $each;
        }
    }

    /**
     * @return string
     */
    public function locateIdMap()
    {
        /** @var array $struct */
        $struct = require $this->getPlatformSetting($this->gameVersion, 'struct');
        assert(is_array($struct));
        assert(isset($struct['idmapDbItem']));

        return trim($struct['idmapDbItem']);
    }

    /**
     * @return array
     */
    public function getGlobalShard()
    {
        /** @var array $struct */
        $struct = require $this->getPlatformSetting($this->gameVersion, 'struct');
        assert(is_array($struct));
        assert(isset($struct['idmapDbItem']));
        $globalShardId = $struct['idmapDbItem'];

        /** @var array $all */
        $all = require $this->getPlatformSetting($this->gameVersion, 'database');
        assert(is_array($all));
        assert(isset($all[$globalShardId]));

        return (array) $all[$globalShardId];
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
        }));

        $this->mapping = [];
        foreach ($dirs as $dir) {
            $version = substr($dir, strrpos($dir, '_') + 1);
            $this->mapping[$version] = $this->onlineSettingDir.'/'.$dir;
        }
    }

    /**
     * @param string $version
     * @param string $name
     *
     * @return string
     */
    protected function getPlatformSetting($version, $name)
    {
        $filename = $this->mapping[$version].'/'.$name.'.php';
        assert(is_file($filename) && is_readable($filename));

        return $filename;
    }
}
