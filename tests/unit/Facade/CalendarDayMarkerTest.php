<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 19:01.
 */
namespace unit\Facade;

use Facade\CalendarDayMarker;

/**
 * Class CalendarDayMarkerTest.
 */
class CalendarDayMarkerTest extends \PHPUnit_Framework_TestCase
{
    protected static $dir;

    public static function setUpBeforeClass()
    {
        self::$dir = __DIR__.'/marker';
        mkdir(self::$dir);
    }

    public static function tearDownAfterClass()
    {
        rmdir(self::$dir);
    }

    public function test()
    {
        $marker = new CalendarDayMarker(self::$dir);
        $marker->reset();

        $date = new \DateTime();
        $ret = $marker->isMarked($date);
        $this->assertNotTrue($ret);

        $marker->mark($date);
        $ret = $marker->isMarked($date);
        $this->assertTrue($ret);

        $marker->reset();
    }
}
