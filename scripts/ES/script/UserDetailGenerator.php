<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/31
 * Time: 17:05.
 */
namespace script;

use PDO;

/**
 * Class UserDetailGenerator.
 */
class UserDetailGenerator
{
    /**
     * @param string $gameVersion
     * @param array  $groupedUidList
     *
     * @return array
     */
    public static function generate($gameVersion, array $groupedUidList)
    {
        $generator = ShardHelper::platformFactory()->getMySQLShards($gameVersion);

        $shardConfigList = [];
        foreach ($generator as $shardConfig) {
            $shardConfigList[$shardConfig['shardId']] = $shardConfig;
        }

        $userList = [];
        foreach ($groupedUidList as $shardId => $uidList) {
            $pdo = ShardHelper::pdoFactory($shardConfigList[$shardId]);
            if ($pdo === false) {
                continue;
            }
            $userList[$shardId] = self::readUserInfo($pdo, $uidList, 100);
        }

        return $userList;
    }

    /**
     * @param PDO   $pdo
     * @param array $uidList
     * @param int   $concurrentLevel
     *
     * @return array
     */
    protected static function readUserInfo(PDO $pdo, array $uidList, $concurrentLevel = 100)
    {
        $result = [];

        $offset = 0;
        while (($concurrent = array_splice($uidList, $offset, $concurrentLevel))) {
            $uids = implode(',', $concurrent);

            $statement = $pdo->query('SELECT * from tbl_user where uid in ('.$uids.')');
            if ($statement === false) {
                appendLog('PDO Statement Error: '.json_encode($pdo->errorInfo()));
                continue;
            }

            $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allRows as $row) {
                $result[$row['uid']] = $row;
            }
            $statement->closeCursor();
        }

        return $result;
    }
}
