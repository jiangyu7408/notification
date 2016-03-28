<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:12 PM.
 */
namespace Worker\Model;

/**
 * Class Task.
 */
class Task
{
    /**
     * @var string
     */
    protected $url;
    /**
     * @var array
     */
    protected $options;

    /**
     * @return string
     */
    public function uniqueId()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
