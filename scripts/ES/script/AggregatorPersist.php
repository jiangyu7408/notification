<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:06.
 */
namespace script;

/**
 * Class AggregatorPersist.
 */
class AggregatorPersist
{
    /** @var string */
    protected $filePath;

    /**
     * AggregatorPersist constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return array
     */
    public function load()
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $jsonData = file_get_contents($this->filePath);
        $payload = json_decode($jsonData, true);

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param array $payload
     *
     * @return int
     */
    public function save(array $payload)
    {
        return file_put_contents($this->filePath, json_encode($payload));
    }
}
