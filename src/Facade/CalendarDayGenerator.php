<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 11:45.
 */
namespace Facade;

/**
 * Class CalendarDayGenerator.
 */
class CalendarDayGenerator
{
    /**
     * @param int $fromTs
     * @param int $toTs
     *
     * @return \Generator
     */
    public static function generate($fromTs, $toTs)
    {
        $firstDay = new \DateTimeImmutable(date('Y-m-d', $fromTs));
        $lastDay = new \DateTimeImmutable(date('Y-m-d', $toTs));

        $cursor = new \DateTime($lastDay->format('Y-m-d'));
        while ($cursor >= $firstDay) {
            yield $cursor->format('Y-m-d');
            $cursor->sub(new \DateInterval('P1D'));
        }
    }
}
