<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 5:00 PM
 */
namespace Persistency;

use FBGateway\FBGatewayFactory;
use FBGateway\FBGatewayParam;
use Persistency\Audit\AuditStorage;

class FBGatewayPersistTest extends \PHPUnit_Framework_TestCase
{
    protected $snsid;
    protected $appid;
    protected $secretKey;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    protected function tearDown()
    {
    }

    protected function setUp()
    {
        /*
         * comes from https://apps.facebook.com/farm_test_jy/
         */
        $this->snsid     = '100001349218797';
        $this->appid     = '611186132271595';
        $this->secretKey = '8c43357c9a0425192f9af574d8c9fa06';
    }

    /**
     * @return FBGatewayPersist
     */
    public function testBuilder()
    {
        $param            = new FBGatewayParam();
        $param->appid     = $this->appid;
        $param->secretKey = $this->secretKey;

        $factory = new FBGatewayFactory($param);

        $auditStorage = new AuditStorage();
        $gateway      = new FBGatewayPersist($factory, $auditStorage);

        return $gateway;
    }

    /**
     * @depends testBuilder
     * @param FBGatewayPersist $gateway
     */
    public function testRetrieve(FBGatewayPersist $gateway)
    {
        $retrieve = $gateway->retrieve();
        static::assertTrue(is_array($retrieve));
        static::assertCount(0, $retrieve);
    }

    /**
     * @depends testBuilder
     * @param FBGatewayPersist $gateway
     */
    public function testFactory(FBGatewayPersist $gateway)
    {
        $factory = $gateway->getFactory();
        $snsid   = $this->snsid;
        $url     = $factory->makeUrl($snsid);
        static::assertTrue(is_string($url));
        static::assertTrue(strpos($url, $snsid) !== false);

        $payload = array(
            'snsid'    => $snsid,
            'template' => 'template123',
            'trackRef' => 'trackRef123',
        );

        $package = $factory->package($payload);
        static::assertTrue(is_array($package));
    }

    /**
     * @depends testBuilder
     * @param FBGatewayPersist $gateway
     */
    public function testPersist(FBGatewayPersist $gateway)
    {
        $snsid   = $this->snsid;
        $payload = array(
            'snsid'    => $snsid,
            'template' => "@[{$snsid}] your submarine has arrived!",
            'trackRef' => 'sub_back',
        );

        $auditStorage = $gateway->getAuditStorage();
        static::assertInstanceOf('Persistency\IPersistency', $auditStorage);

        $success = $gateway->persist($payload);

        $auditItems = $auditStorage->retrieve();
        static::assertTrue(is_array($auditItems));
        static::assertCount(1, $auditItems);
        $auditItem = array_pop($auditItems);
        static::assertArrayHasKey('success', $auditItem);

        static::assertTrue($success, 'persist failed');
        static::assertTrue($auditItem['success']);
    }

    public function testFailedPersist()
    {
        $param            = new FBGatewayParam();
        $param->appid     = $this->appid;
        $param->endpoint  = 'http://nowhere';
        $param->secretKey = $this->secretKey;

        $factory = new FBGatewayFactory($param);

        $auditStorage = new AuditStorage();
        $gateway      = new FBGatewayPersist($factory, $auditStorage);
        static::assertInstanceOf('Persistency\FBGatewayPersist', $gateway);

        $snsid   = $this->snsid;
        $payload = array(
            'snsid'    => $snsid,
            'template' => "@[{$snsid}] your submarine has arrived!",
            'trackRef' => 'trackRef123',
        );

        $auditStorage = $gateway->getAuditStorage();
        static::assertInstanceOf('Persistency\IPersistency', $auditStorage);

        $success = $gateway->persist($payload);
        static::assertNotTrue($success);

        $auditItems = $auditStorage->retrieve();
        static::assertTrue(is_array($auditItems));
        static::assertCount(1, $auditItems);
        $auditItem = array_pop($auditItems);
        static::assertArrayHasKey('success', $auditItem);
        static::assertNotTrue($auditItem['success']);
    }
}
