<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 19:22.
 */
namespace DataProvider\User;

use PDO;
use SplObjectStorage;

/**
 * Class QueryHelperFactory.
 */
class QueryHelperFactory extends QueryHelper
{
    /** @var SplObjectStorage */
    protected static $instanceContainer;
    /** @var bool */
    protected static $createVerbose = false;

    /**
     * @param PDO $pdo
     *
     * @return QueryHelper
     */
    public static function make(PDO $pdo)
    {
        $queryHelper = self::getInstance($pdo);

        return $queryHelper;
    }

    /**
     * @param bool $verbose
     */
    public static function setVerbose($verbose)
    {
        self::$createVerbose = (bool) $verbose;
    }

    /**
     * @param PDO $pdo
     *
     * @return QueryHelper
     */
    protected static function getInstance(PDO $pdo)
    {
        if (self::$instanceContainer === null) {
            self::$instanceContainer = new SplObjectStorage();
        }

        if (!isset(self::$instanceContainer[$pdo])) {
            $queryHelper = new QueryHelper($pdo, self::$createVerbose);
            self::$instanceContainer[$pdo] = $queryHelper;
        }

        return self::$instanceContainer[$pdo];
    }
}
