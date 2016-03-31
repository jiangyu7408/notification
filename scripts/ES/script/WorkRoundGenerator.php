<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:34.
 */
namespace script;

use Timer\Generator;

/**
 * Class WorkRoundGenerator.
 */
class WorkRoundGenerator
{
    /**
     * @param int  $lastActiveTimestamp
     * @param int  $quitTimestamp
     * @param int  $interval
     * @param bool $verbose
     *
     * @return \Generator
     */
    public static function generate($lastActiveTimestamp, $quitTimestamp, $interval, $verbose)
    {
        $timer = (new Generator())->shootThenGo($lastActiveTimestamp, $quitTimestamp);

        $step = 0;
        $firstRun = true;
        foreach ($timer as $timestamp) {
            if ($firstRun) {
                $firstRun = !$firstRun;
                yield $lastActiveTimestamp;
                continue;
            }

            ++$step;
            if ($verbose) {
                dump(date('c', $timestamp).' step = '.$step);
            }

            if ($step >= $interval) {
                $step = 0;
                $lastRoundTs = $timestamp - $interval - 1;
                yield $lastRoundTs;
            }
        }
    }
}
