<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:58 PM.
 */
namespace ESGateway;

use Elastica\Client;

require __DIR__.'/../../library/ip2cc.php';

/**
 * Class Factory.
 */
class Factory
{
    /**
     * @var callable
     */
    protected $intValidator;
    protected $fieldMapping = [
        'int' => [
            'uid',
            'loginnum',
            'continuous_day',
            'experience',
            'gas',
            'greenery',
            'level',
            'coins',
            'new_cash1',
            'new_cash2',
            'new_cash3',
            'op',
            'reward_points',
            'sign_points',
            'size_x',
            'status',
            'top_map_size',
            'water_exp',
            'water_level',
            'silver_coins',
            'reputation',
            'reputation_level',
            'vip_level',
            'vip_points',
            'pay_times',
            'history_pay_amount',
            'last_pay_amount',
        ],
        'string' => [
            'addtime',
            'logintime',
            'last_pay_time',
        ],
    ];

    /**
     * Factory constructor.
     */
    public function __construct()
    {
        $intValidator = function ($input) {
            if ($input > 2147483647) {
                return 2147483647;
            }

            return (int) $input;
        };
        $stringValidator = function ($input) {
            return trim($input);
        };

        foreach ($this->fieldMapping['int'] as $field) {
            $this->fieldMapping[$field] = $intValidator;
        }
        foreach ($this->fieldMapping['string'] as $field) {
            if (isset($this->fieldMapping[$field])) {
                throw new \LogicException('bad logic setting found on field: '.$field);
            }
            $this->fieldMapping[$field] = $stringValidator;
        }
    }

    /**
     * @param string $host
     * @param int    $port
     *
     * @return Client
     */
    public function makeClient($host, $port)
    {
        return new Client(
            [
                'host' => $host,
                'port' => $port,
            ]
        );
    }

    /**
     * @param string $index
     * @param string $typeName
     *
     * @return Type
     */
    public function makeType($index, $typeName)
    {
        assert(is_string($index) && strlen($index) > 0);
        assert(is_string($typeName) && strlen($typeName) > 0);

        $type = new Type($index, $typeName);

        return $type;
    }

    /**
     * @param array $dbEntity
     *
     * @return User
     */
    public function makeUser(array $dbEntity)
    {
        $dbEntity['name'] = utf8_encode($dbEntity['name']);
        $dbEntity['country'] = isset($dbEntity['country']) ? $dbEntity['country'] : ip2cc($dbEntity['loginip']);
        $dbEntity['addtime'] = $this->sanityTimeString($dbEntity['addtime']);
        $dbEntity['logintime'] = $this->sanityTimeString($dbEntity['logintime']);
        if (array_key_exists('last_pay_time', $dbEntity)) {
            $dbEntity['last_pay_time'] = $this->sanityTimeString($dbEntity['last_pay_time']);
        }
        $dbEntity['chef_level'] = 0;
        $dbEntity['picture'] = '';

        $user = new User();
        $keys = array_keys(get_object_vars($user));
        foreach ($keys as $key) {
            if (!array_key_exists($key, $dbEntity)) {
                continue;
            }
            if (!isset($this->fieldMapping[$key])) {
                $user->{$key} = $dbEntity[$key];
                continue;
            }
            $user->{$key} = call_user_func($this->fieldMapping[$key], $dbEntity[$key]);
        }
        if ($user->loginip === '') {
            $user->loginip = '127.0.0.1';
        }

        return $user;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function toArray(User $user)
    {
        $array = get_object_vars($user);
        ksort($array, SORT_STRING);

        return $array;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function sanityTimeString($input)
    {
        if (is_numeric($input)) {
            return (string) date('Ymd\\THisO', $input);
        }
        if (is_string($input) && strpos($input, '+') === false) {
            return date_create($input)->format('Ymd\\THisO');
        }

        return $input;
    }
}
