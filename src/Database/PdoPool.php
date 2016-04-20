<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/20
 * Time: 10:59.
 */
namespace Database;

use PDO;

/**
 * Class PdoPool.
 */
class PdoPool
{
    /** @var PDO[] $connections */
    protected static $connections = [];
    /** @var array */
    protected $shardOptions;

    /**
     * PdoPool constructor.
     *
     * @param array $shardOptions
     */
    protected function __construct(array $shardOptions)
    {
        $this->shardOptions = $shardOptions;
    }

    /**
     * @param string $shardId
     *
     * @return false|PDO
     */
    public function getByShardId($shardId)
    {
        assert(is_string($shardId) && strpos($shardId, 'db') === 0);
        if (!array_key_exists($shardId, $this->shardOptions)) {
            throw new \InvalidArgumentException(sprintf('shardId %s not found', $shardId));
        }
        $option = $this->shardOptions[$shardId];

        return $this->getByOption($option);
    }

    /**
     * @param array $option
     *
     * @return false|PDO
     */
    public function getByOption(array $option)
    {
        $dsn = $this->makeDsn($option);
        if (!array_key_exists($dsn, self::$connections)) {
            $pdo = $this->connect($dsn, $option);
            if ($pdo instanceof PDO) {
                self::$connections[$dsn] = $pdo;
            }

            return $pdo;
        }

        $pdo = self::$connections[$dsn];
        if (is_bool($pdo)) {
            unset(self::$connections[$dsn]);

            return $this->getByOption($dsn);
        }

        self::$connections[$dsn] = $this->reconnectIfNeeded($pdo, $dsn, $option);

        return self::$connections[$dsn];
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function makeDsn(array $options)
    {
        $dsn = sprintf('mysql:dbname=%s;host=%s', trim($options['database']), trim($options['host']));

        return $dsn;
    }

    /**
     * @param string $dsn
     * @param array  $options
     *
     * @return false|PDO
     */
    private function connect($dsn, array $options)
    {
        try {
            $pdo = new PDO(
                $dsn,
                $options['username'],
                $options['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 1,
                    PDO::ATTR_CASE => PDO::CASE_NATURAL,
                ]
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (\PDOException $e) {
            appendLog(sprintf('Error on dsn[%s]: %s', $dsn, $e->getMessage()));

            return false;
        }
    }

    /**
     * @param PDO    $pdo
     * @param string $dsn
     * @param array  $options
     *
     * @return false|PDO
     */
    private function reconnectIfNeeded(PDO $pdo, $dsn, array $options)
    {
        try {
            $pdo->query('SHOW STATUS;')->execute();
        } catch (\PDOException $e) {
            if ($e->getCode() != 'HY000' || !stristr($e->getMessage(), 'server has gone away')) {
                throw $e;
            }

            return $this->connect($dsn, $options);
        }

        return $pdo;
    }
}
