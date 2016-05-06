<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 11:40.
 */
namespace Facade\ElasticSearch;

use Elastica\Document;
use ESGateway\User;

/**
 * Class DocumentFactory.
 */
class DocumentFactory
{
    /** @var array */
    protected $fieldList = [];
    /** @var Document */
    protected $docPrototype;

    /**
     * DocumentFactory constructor.
     *
     * @param Document $prototype
     */
    public function __construct(Document $prototype)
    {
        if ($prototype->getIndex() === '' || $prototype->getType() === '') {
            throw new \InvalidArgumentException('prototype not properly inited');
        }
        $this->docPrototype = $prototype;
        $fieldValuePairs = get_object_vars(new User());
        array_walk($fieldValuePairs, function ($value, $field) {
            $this->fieldList[$field] = gettype($value);
        });
    }

    /**
     * @param string $snsid
     * @param array  $rawUserInfo
     *
     * @return Document
     */
    public function make($snsid, array $rawUserInfo)
    {
        if (!isset($rawUserInfo['snsid'])) {
            throw new \InvalidArgumentException('snsid not found: '.json_encode($rawUserInfo));
        }
        assert($snsid === $rawUserInfo['snsid'], print_r($rawUserInfo, true));

        $payload = $this->buildPayload($rawUserInfo);

        return $this->buildDocument($snsid, $payload);
    }

    /**
     * @param array $rawUserInfo
     *
     * @return array
     */
    public function buildPayload(array $rawUserInfo)
    {
        $userInfo = [];
        foreach ($this->fieldList as $field => $type) {
            if (!array_key_exists($field, $rawUserInfo)) {
                continue;
            }
            $value = $this->sanitizeValue($field, $type, $rawUserInfo);
            $userInfo[$field] = $this->sanitizeTime($field, $value);
        }

        $status = array_key_exists('status', $userInfo) ? (int) $userInfo['status'] : 1;
        $simplified = array_filter($userInfo);
        $simplified['status'] = $status;
        $simplified['country'] = $this->parseCountry($rawUserInfo);

        return $simplified;
    }

    /**
     * @param string $snsid
     * @param array  $payload
     *
     * @return Document
     */
    public function buildDocument($snsid, array $payload)
    {
        $document = clone $this->docPrototype;
        $document->setId($snsid)
                 ->setData($payload)
                 ->setDocAsUpsert(true);

        return $document;
    }

    /**
     * @param string $field
     * @param string $type
     * @param array  $rawUserInfo
     *
     * @return float|int|string
     */
    protected function sanitizeValue($field, $type, array $rawUserInfo)
    {
//        dump(sprintf('%30s %20s %20s', $field, $rawUserInfo[$field], $type));
        if ($type === 'string') {
            return trim($rawUserInfo[$field]);
        }
        if ($type === 'integer') {
            return (int) $rawUserInfo[$field];
        }
        if ($type === 'double') {
            return (float) $rawUserInfo[$field];
        }
        throw new \LogicException(sprintf('type %s not supported', $type));
    }

    /**
     * @param string $field
     * @param string $input
     *
     * @return string
     */
    protected function sanitizeTime($field, $input)
    {
        if (!in_array($field, ['addtime', 'logintime', 'last_pay_time'])) {
            return $input;
        }
        if (is_numeric($input)) {
            return (string) date('Ymd\\THisO', $input);
        }
        if (is_string($input) && strpos($input, '+') === false) {
            return date_create($input)->format('Ymd\\THisO');
        }

        return $input;
    }

    /**
     * @param array $dbEntity
     *
     * @return string
     */
    private function parseCountry(array $dbEntity)
    {
        if (isset($dbEntity['country'])) {
            $sanitized = trim($dbEntity['country']);
            if ($sanitized) {
                return $sanitized;
            }
        }

        $unknown = 'UNKNOWN';
        if (!isset($dbEntity['loginip'])) {
            return $unknown;
        }

        if (!function_exists('ip2cc')) {
            require CONFIG_DIR.'/library/ip2cc.php';
        }

        $possibleCountry = ip2cc($dbEntity['loginip']);

        return is_string($possibleCountry) && strlen($possibleCountry) > 0 ? $possibleCountry : $unknown;
    }
}
