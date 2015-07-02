<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/30
 * Time: 11:11 AM
 */
namespace Queue;

use InvalidArgumentException;

class FileQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileQueue
     */
    protected $queue;
    /**
     * @var string
     */
    protected $dir;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testBogus()
    {
        $queue = new FileQueue('__fixture_bogus');
        static::assertNull($queue);
    }

    public function testSingle()
    {
        $queue   = new FileQueue($this->dir);
        $msg     = 'test' . time() . PHP_EOL;
        $success = $queue->push($msg);
        static::assertTrue($success);

        $info = $queue->pop();
        static::assertEquals($msg, $info);
    }

    public function testMulti()
    {
        $msgList = [];
        for ($i = 0; $i < 10; $i++) {
            $msgList[] = 'queue_item_' . $i;
        }

        $queue = new FileQueue($this->dir);

        foreach ($msgList as $msg) {
            $ret = $queue->push($msg);
            static::assertTrue($ret);
        }

        $offset = 0;
        while ($msg = $queue->pop()) {
            static::assertEquals($msgList[$offset], $msg);
            $offset++;
        }
    }

    protected function tearDown()
    {
        $this->cleanUp($this->dir);
    }

    private function cleanUp($dir)
    {
        if (strpos($dir, 'unit') === false
            || strpos($dir, '__fixture') === false
        ) {
            trigger_error('warning: bad dir => ' . $dir);
        }
        $cmd = "rm -rf $dir/" . date('Y') . '*';
        exec($cmd);
    }

    protected function setUp()
    {
        $this->dir = __DIR__ . '/__fixture';
        $this->cleanUp($this->dir);
    }
}
