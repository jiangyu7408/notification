<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:58 PM.
 */
namespace ESGateway;

require __DIR__.'/../../library/ip2cc.php';

use Elasticsearch\Client;

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
            'addtime',
            'coins',
            'continuous_day',
            'experience',
            'gas',
            'greenery',
            'level',
            'loginnum',
            'logintime',
            'new_cash1',
            'new_cash2',
            'new_cash3',
            'op',
            'pay_times',
            'reward_points',
            'sign_points',
            'size_x',
            'status',
            'top_map_size',
            'water_exp',
            'water_level',
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

            return $input;
        };

        foreach ($this->fieldMapping['int'] as $field) {
            $this->fieldMapping[$field] = $intValidator;
        }
    }

    /**
     * @param string $dsn
     *
     * @return Client
     */
    public function makeClient($dsn)
    {
        assert(is_string($dsn) && strlen($dsn) > 7);

        return new Client(
            [
                'hosts' => [$dsn],
            ]
        );
    }

    /**
     * @param string $ipAddress
     * @param int    $port
     *
     * @return string
     */
    public function makeDsn($ipAddress, $port)
    {
        return $this->makeDsnObject($ipAddress, $port)->toString();
    }

    /**
     * @param string $ipAddress
     * @param int    $port
     *
     * @return DSN
     */
    public function makeDsnObject($ipAddress, $port)
    {
        assert(is_string($ipAddress) && strlen($ipAddress) > 7);
        assert(is_int($port) && ($port > 1024 && $port < 65535));

        $dsn = new DSN();
        $dsn->port = $port;
        $dsn->ip = $ipAddress;

        return $dsn;
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

        $type = new Type();
        $type->index = $index;
        $type->type = $typeName;

        return $type;
    }

    /**
     * @param array $dbEntity
     *
     * @return User
     */
    public function makeUser(array $dbEntity)
    {
        $user = new User();
        $keys = array_keys(get_object_vars($user));

        $dbEntity['name'] = utf8_encode($dbEntity['name']);
        $dbEntity['country'] = ip2cc($dbEntity['loginip']);
        $dbEntity['addtime'] = $this->sanityTimeString($dbEntity['addtime']);
        $dbEntity['logintime'] = $this->sanityTimeString($dbEntity['logintime']);
        $dbEntity['chef_level'] = 0;
        $dbEntity['picture'] = '';

        foreach ($keys as $key) {
            if (!isset($this->fieldMapping[$key])) {
                $user->$key = $dbEntity[$key];
                continue;
            }
            $user->$key = call_user_func($this->fieldMapping[$key], $dbEntity[$key]);
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
