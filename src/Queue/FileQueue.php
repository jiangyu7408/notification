<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 10:25 AM
 */

namespace Queue;

/**
 * Use a dir as a queue container.
 * Each file in this dir as an item of a queue.
 * Class FileQueue
 * @package Queue
 */
class FileQueue implements IQueue
{
    /**
     * @var string
     */
    protected $location;

    /**
     * @param string $location
     * @throw InvalidArgumentException
     */
    public function __construct($location)
    {
        try {
            $this->location = $this->prepareDir($location);
        } catch (\InvalidArgumentException $e) {
            // TODO: error handling
            throw $e;
        }
    }

    /**
     * @param string $baseDir
     * @return string
     * @throws \InvalidArgumentException
     */
    private function prepareDir($baseDir)
    {
        if (!is_dir($baseDir)) {
            throw new \InvalidArgumentException($baseDir . ' supposed to be a dir');
        }
        $dir = $baseDir . '/' . date('Ymd');
        if (!is_dir($dir)) {
            $ret = mkdir($dir, 0777, true);
            if ($ret === false) {
                throw new \InvalidArgumentException("can't have a dir for file[$baseDir]");
            }
        }
        return $dir;
    }

    /**
     * @param string $msg
     * @return bool
     */
    public function push($msg)
    {
        $filename = $this->location . '/' . date('His') . '_' . microtime(true);
        xdebug_debug_zval('filename');
        if (is_string($msg)) {
            return $this->writeFile($filename, $msg);
        }
        return $this->writeFile($filename, json_encode($msg));
    }

    /**
     * @param $filename
     * @param $msgString
     * @return bool
     */
    private function writeFile($filename, $msgString)
    {
        $ret = file_put_contents($filename, $msgString);
        return ($ret !== false);
    }

    /**
     * @return string
     */
    public function pop()
    {
        $file = $this->firstFileInDir($this->location);
        if ($file === null) {
            // TODO: error handling
            return '';
        }

        if ($file === '') {
            return '';
        }

        $content = file_get_contents($file);
        unlink($file);
        return $content;
    }

    /**
     * @param string $dir
     * @return null|string
     */
    private function firstFileInDir($dir)
    {
        $files = scandir($dir, SCANDIR_SORT_ASCENDING);
        if ($files === false) {
            return null;
        }

        $fileCount = count($files);

        if ($fileCount === 2) { // bypass dirs: '.' '..'
            return '';
        }

        for ($i = 2; $i < $fileCount; $i++) {
            $file = $dir . '/' . $files[$i];
            if (is_file($file)) {
                return $file;
            }
        }

        return '';
    }
}