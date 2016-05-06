<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/27
 * Time: 11:58.
 */
namespace unit\Facade;

use Elastica\Document;
use Facade\ElasticSearch\DocumentFactory;

/**
 * Class ElasticSearchDocumentFactoryTest.
 */
class ElasticSearchDocumentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var Document */
    protected static $documentPrototype;

    /**
     *
     */
    public static function setUpBeforeClass()
    {
        require_once __DIR__.'/../ES/UserProvider.php';

        self::$documentPrototype = new Document();
        self::$documentPrototype->setIndex('farm')
                                ->setType('user:tw');
    }

    /**
     *
     */
    public function test()
    {
        $jsonDataList = \UserProvider::getJsonData();
        $rawUserInfoList = [];
        foreach ($jsonDataList as $json) {
            $arr = json_decode($json, true);
            $this->assertTrue(is_array($arr));
            $rawUserInfoList[] = $arr;
        }

        $docFactory = new DocumentFactory(self::$documentPrototype);
        foreach ($rawUserInfoList as $rawUserInfo) {
            $payload = $docFactory->buildPayload($rawUserInfo);

            $this->assertArrayNotHasKey('name', $payload, print_r($payload, true));
            $this->assertArrayNotHasKey('email', $payload, print_r($payload, true));
            $this->assertArrayNotHasKey('track_ref', $payload, print_r($payload, true));
            $this->assertArrayNotHasKey('loginip', $payload, print_r($payload, true));

            foreach ($payload as $field => $value) {
                if ($field === 'status') {
                    $this->assertTrue(is_int($value) && $value >= 0);
                    continue;
                }
                $this->assertNotEmpty($value, sprintf('field [%s] should not be empty', $field));
            }
        }
    }

    /**
     *
     */
    public function testPartial()
    {
        $docFactory = new DocumentFactory(self::$documentPrototype);

        $userInfo = [
            'snsid' => '100001349218797',
            'name' => 'jiangyu',
            'email' => 'jiangyu7408@gmail.com',
            'country' => 'CN',
        ];
        $payload = $docFactory->buildPayload($userInfo);

        $this->assertArrayNotHasKey('name', $payload, print_r($payload, true));
        $this->assertArrayNotHasKey('email', $payload, print_r($payload, true));
        $this->assertArrayHasKey('country', $payload, print_r($payload, true));

        $document = $docFactory->buildDocument($userInfo['snsid'], $payload);
        $this->assertNotEmpty($document->getIndex(), 'Bad index '.print_r($document, true));
        $this->assertNotEmpty($document->getType(), 'Bad type '.print_r($document, true));
    }
}
