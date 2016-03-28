<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/28
 * Time: 16:13.
 */
namespace Worker\Queue;

/**
 * Class HttpTracer.
 */
class HttpTracer
{
    /** @var int */
    protected $httpStatus;
    /** @var float */
    protected $startTs;
    /** @var float */
    protected $stopTs;
    /** @var float */
    protected $networkLatency;
    /** @var float */
    protected $serverLatency;
    /** @var array */
    protected $profile = [];

    /**
     * @return float
     */
    public function start()
    {
        $this->startTs = microtime(true);

        return $this->startTs;
    }

    /**
     * @param array $info
     */
    public function stop(array $info)
    {
        $this->httpStatus = $info['http_code'];
        $this->stopTs = $this->startTs + $info['total_time'];
        $this->networkLatency = $info['namelookup_time'] + $info['connect_time'];
        $this->serverLatency = $info['starttransfer_time'] - $info['pretransfer_time'];

        array_walk($info, function ($value, $key) {
            if (strpos($key, 'time') !== false) {
                $this->profile[$key] = $value;
            }
        });
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return [
            'start' => $this->startTs,
            'stop' => $this->stopTs,
            'network_latency' => $this->networkLatency,
            'server_latency' => $this->serverLatency,
            'profile' => $this->profile,
        ];
    }

    /**
     * @return float
     */
    public function getElapsedTime()
    {
        return $this->stopTs - $this->startTs;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            'code: %d, start: %f, stop: %f, network: %f, server: %f',
            $this->httpStatus,
            $this->startTs,
            $this->stopTs,
            $this->networkLatency,
            $this->serverLatency
        );
    }
}
