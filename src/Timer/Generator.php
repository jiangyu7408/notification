<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/03
 * Time: 3:21 PM
 */

namespace Timer;

/**
 * Class Generator
 * @package Timer
 */
class Generator
{
    /**
     * @param int $shootTime
     * @param int $stopTime
     * @return \Generator
     */
    public function shootThenGo($shootTime, $stopTime)
    {
        $entry     = time();
        $timestamp = $shootTime;
        while (true) {
            if ($timestamp > $stopTime) {
                break;
            }

            if ($timestamp > time()) {
                time_sleep_until($timestamp);
            }

            yield $timestamp;

            if ($timestamp < $entry) {
                $timestamp = $entry;
                continue;
            }

            $timestamp++;
        }
    }
}
