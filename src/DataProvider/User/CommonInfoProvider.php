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
     * @param PDO   $pdo
     * @param array $uidList
     * @param int   $concurrentLevel
     *
     * @return array
     */
    public static function readUserInfo(PDO $pdo, array $uidList, $concurrentLevel = 100)
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
