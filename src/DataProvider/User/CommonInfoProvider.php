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
     * @return array
     */
    public static function readUserInfo(PDO $pdo, array $uidList, $concurrentLevel = 100, $columns = '*')
    {
        $uninstalledList = self::fetchUninstalled($pdo);

        $result = [];

        $offset = 0;
        while (($concurrent = array_splice($uidList, $offset, $concurrentLevel))) {
            $placeHolderList = array_pad([], count($concurrent), '?');
            $sql = sprintf('SELECT %s from tbl_user WHERE uid IN (%s)', $columns, implode(',', $placeHolderList));
            $statement = $pdo->prepare($sql);
            if ($statement === false) {
                throw new \RuntimeException(json_encode($pdo->errorInfo()));
            }
            $success = $statement->execute($concurrent);
            assert($success);

            $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allRows as $row) {
                $uid = (int) $row['uid'];
                if (array_key_exists($uid, $uninstalledList)) {
                    $row['status'] = 0;
                    appendLog($uid.' => uninstalled');
                }
                $result[$uid] = $row;
            }
            $statement->closeCursor();
        }

        return $result;
    }

    /**
     * @param PDO $pdo
     *
     * @return array [uid, uid]
     */
    protected static function fetchUninstalled(PDO $pdo)
    {
        $sql = 'SELECT uid FROM tbl_user_remove_log where uid>0 limit 4';
        $statement = $pdo->prepare($sql);
        $statement->execute();

        $uidList = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_flip($uidList);
    }
}
