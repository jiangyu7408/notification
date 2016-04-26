<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 11:10.
 */
namespace DataProvider\User;

use PDO;

/**
 * Class CommonInfoProvider.
 */
class CommonInfoProvider
{
    /**
     * @param PDO    $pdo
     * @param array  $uidList
     * @param int    $concurrentLevel
     * @param string $columns
     *
     * @return \Generator
     */
    public static function readUserInfo(PDO $pdo, array $uidList, $concurrentLevel = 100, $columns = '*')
    {
        $queryHelper = QueryHelperFactory::make($pdo);
        $removedUidSnsidPairs = $queryHelper->listUninstalledUid();

        while (($batch = array_splice($uidList, 0, $concurrentLevel))) {
            $dataSet = $queryHelper->readUserInfo($batch, $removedUidSnsidPairs, $columns);
            yield $dataSet;
        }
    }
}
