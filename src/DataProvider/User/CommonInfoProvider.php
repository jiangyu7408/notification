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
                $result[$row['uid']] = $row;
            }
            $statement->closeCursor();
        }

        return $result;
    }
}
