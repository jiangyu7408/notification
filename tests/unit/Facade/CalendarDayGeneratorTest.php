<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 11:50.
 */
namespace unit\Facade;

use Facade\CalendarDayGenerator;

/**
 * Class CalendarDayGeneratorTest.
 */
class CalendarDayGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testGenerate()
    {
        $now = time();
//        $fromTs = $now - 90 * 3600 * rand(10, 20);
        $fromTs = $now - 3600;
        $toTs = $now;
        $dayGenerator = CalendarDayGenerator::generate($fromTs, $toTs);
        $days = [];
        foreach ($dayGenerator as $day) {
            $days[] = $day;
        }
        $diff = (new \DateTime(date('Y-m-d', $fromTs)))->diff((new \DateTime(date('Y-m-d', $toTs))));
        $expectedDays = $diff->days + 1;
        $this->assertEquals($expectedDays, count($days));
    }
}
