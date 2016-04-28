<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/28
 * Time: 16:42.
 */
namespace DataProvider\User;

/**
 * Class LoginUidProvider.
 */
class LoginUidProvider extends InstallUidProvider
{
    /**
     * @param \PDO   $pdo
     * @param string $date
     *
     * @return array [uid, uid]
     */
    protected function fetchNewUser(\PDO $pdo, $date)
    {
        $dateTime = new \DateTime($date);
        $dateTime->setTime(0, 0, 0);
        $fromTs = $dateTime->getTimestamp();
        $dateTime->setTime(23, 59, 59);
        $toTs = $dateTime->getTimestamp();

        $sql = 'SELECT uid FROM tbl_user WHERE logintime>=? AND logintime<=?';
        $statement = $pdo->prepare($sql);
        $statement->execute([$fromTs, $toTs]);

        $uidList = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return $uidList;
    }
}
