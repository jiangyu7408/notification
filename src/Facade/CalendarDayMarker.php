<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 18:45.
 */
namespace Facade;

/**
 * Class CalendarDayMarker.
 */
class CalendarDayMarker
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
     *
     */
    public function reset()
    {
        $files = scandir($this->filePath);
        array_map(
            function ($file) {
                dump($file);
                if ($file === '.' || $file === '..' || strrpos($file, '.php') !== false) {
                    return;
                }
                unlink($this->filePath.'/'.$file);
            },
            $files
        );
    }

    /**
     * @param \DateTimeInterface $date
     */
    public function mark(\DateTimeInterface $date)
    {
        if ($this->isMarked($date)) {
            return;
        }

        file_put_contents($this->makeMarker($date), time());
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return bool
     */
    public function isMarked(\DateTimeInterface $date)
    {
        return file_exists($this->makeMarker($date));
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
     * @param \DateTimeInterface $date
     *
     * @return string
     */
    protected function makeMarker(\DateTimeInterface $date)
    {
        return sprintf('%s/%s', $this->filePath, $date->format('Y-m-d'));
    }
}
