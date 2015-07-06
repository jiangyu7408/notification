<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/06
 * Time: 1:58 PM
 */

namespace ESGateway;

use Elasticsearch\Client;

/**
 * Class Factory
 * @package ESGateway
 */
class Factory
{
    /**
     * @param string $dsn
     * @return Client
     */
    public function makeClient($dsn)
    {
        assert(is_string($dsn) && strlen($dsn) > 7);

        return new Client(
            [
                'hosts' => [$dsn]
            ]
        );
    }

    /**
     * @param string $ip
     * @param int $port
     * @return string
     */
    public function makeDsn($ip, $port)
    {
        return $this->makeDsnObject($ip, $port)->toString();
    }

    /**
     * @param string $ip
     * @param int $port
     * @return DSN
     */
    public function makeDsnObject($ip, $port)
    {
        assert(is_string($ip) && strlen($ip) > 7);
        assert(is_int($port) && ($port > 1024 && $port < 65535));

        $dsn       = new DSN();
        $dsn->port = $port;
        $dsn->ip   = $ip;

        return $dsn;
    }

    /**
     * @param string $index
     * @param string $typeName
     * @return Type
     */
    public function makeType($index, $typeName)
    {
        assert(is_string($index) && strlen($index) > 0);
        assert(is_string($typeName) && strlen($typeName) > 0);

        $type        = new Type();
        $type->index = $index;
        $type->type  = $typeName;

        return $type;
    }

    public function makeUser(array $dbEntity)
    {
        $user = new User();
        $keys = array_keys(get_object_vars($user));

//        $dbEntity['country'] = Ip2Country::get($dbEntity['loginip']);
        $dbEntity['country'] = 'todo';

        foreach ($keys as $key) {
            $user->$key = $dbEntity[$key];
        }
        return $user;
    }

    public function toArray(User $user)
    {
        return get_object_vars($user);
    }
}
