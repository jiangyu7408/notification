<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 5:00 PM
 */
namespace Persistency;

use FBGateway\FactoryBuilder;
use Persistency\Audit\AuditStorage;
use Persistency\Facebook\GatewayPersist;

class FBGatewayPersistTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GatewayPersist
     */
    protected $persist;
    /**
     * @var array
     */
    protected $config;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function testRetrieve()
    {
        $balance = $this->getPersistInstance('good')->retrieve();
        static::assertTrue(is_array($balance));
        static::assertCount(0, $balance);
    }

    /**
     * @param string $type
     * @return GatewayPersist
     */
    protected function getPersistInstance($type)
    {
        static::assertTrue(is_string($type));
        static::assertTrue($type === 'good' || $type === 'bad');

        $factory         = (new FactoryBuilder())->create($this->config[$type]);

        $auditStorage    = new AuditStorage();
        $persistInstance = new GatewayPersist($factory, $auditStorage);

        return $persistInstance;
    }

    public function testFactory()
    {
        $factory = $this->getPersistInstance('good')->getFactory();
        $snsid   = $this->config['good']['snsid'];
        $url     = $factory->makeUrl($snsid);
        static::assertTrue(is_string($url));
        static::assertTrue(strpos($url, $snsid) !== false);

        $payload = [
            'snsid'    => $snsid,
            'template' => 'template123',
            'trackRef' => 'trackRef123',
        ];

        $package = $factory->package($payload);
        static::assertTrue(is_array($package));
    }

//    public function testGoodPersist()
//    {
//        $snsid   = $this->config['good']['snsid'];
//        $payload = [
//            'snsid'    => $snsid,
//            'template' => "@[{$snsid}] your submarine has arrived!",
//            'trackRef' => 'sub_back',
//        ];
//
//        $persistInstance = $this->getPersistInstance('good');
//
//        $auditStorage = $persistInstance->getAuditStorage();
//        static::assertInstanceOf(IPersistency::class, $auditStorage);
//
//        $success = $persistInstance->persist($payload);
//        static::assertTrue($success, 'persist failed');
//
//        $auditItems = $auditStorage->retrieve();
//        static::assertTrue(is_array($auditItems));
//        static::assertCount(1, $auditItems);
//        $auditItem = array_pop($auditItems);
//        static::assertArrayHasKey('success', $auditItem);
//        static::assertTrue($auditItem['success']);
//    }

    public function testBadPersist()
    {
        $snsid   = $this->config['bad']['snsid'];
        $gateway = $this->getPersistInstance('bad');
        static::assertInstanceOf(GatewayPersist::class, $gateway);

        $payload = [
            'snsid'    => $snsid,
            'template' => "@[{$snsid}] your submarine has arrived!",
            'trackRef' => 'trackRef123',
        ];

        $auditStorage = $gateway->getAuditStorage();
        static::assertInstanceOf(IPersistency::class, $auditStorage);

        $success = $gateway->persist($payload);
        static::assertNotTrue($success);

        $auditItems = $auditStorage->retrieve();
        static::assertTrue(is_array($auditItems));
        static::assertCount(1, $auditItems);
        $auditItem = array_pop($auditItems);
        static::assertArrayHasKey('success', $auditItem);
        static::assertNotTrue($auditItem['success']);
    }

    public function testNoSnsid()
    {
        $gateway = $this->getPersistInstance('bad');
        static::assertInstanceOf(GatewayPersist::class, $gateway);

        $payload = [
            'template' => 'test no snsid',
            'trackRef' => 'trackRef123',
        ];

        try {
            $success = $gateway->persist($payload);
            static::assertNotTrue($success);
        } catch (\Exception $e) {
            static::assertInstanceOf(\InvalidArgumentException::class, $e);
        }
    }

    protected function tearDown()
    {
    }

    /**
     *
     */
    protected function setUp()
    {
        $this->config = require __DIR__ . '/../_fixture/fb.php';
    }
}
