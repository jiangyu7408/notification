<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/26
 * Time: 11:07.
 */
namespace DataProvider\User;

use PDO;

/**
 * Class QueryHelper.
 */
class QueryHelper
{
    /** @var PDO */
    protected $pdo;
    /** @var int[] */
    protected $uninstalledUidList;
    /** @var bool */
    protected $verbose;
    /** @var string */
    protected $dbName;

    /**
     * QueryHelper constructor.
     *
     * @param PDO  $pdo
     * @param bool $verbose
     */
    protected function __construct(PDO $pdo, $verbose = false)
    {
        $this->pdo = $pdo;
        $this->verbose = $verbose;
        $this->dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
    }

    /**
     * @return array uid snsid pairs [uid => ['snsid' => $snsid, 'uninstallTime' => $uninstallTime],]
     */
    public function listUninstalledUid()
    {
        if ($this->uninstalledUidList) {
            return $this->uninstalledUidList;
        }

        $pdo = $this->pdo;
        $sql = sprintf(
            '/* %s */SELECT uid,snsid,log_time as uninstallTime FROM tbl_user_remove_log WHERE uid>0',
            __METHOD__
        );
        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new \RuntimeException(json_encode($pdo->errorInfo()));
        }
        $statement->execute();

        $dataSet = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->uninstalledUidList = [];
        array_map(
            function (array $row) {
                $uid = (int) $row['uid'];
                $snsid = $row['snsid'];
                $uninstallTime = (int) $row['uninstallTime'];
                if (!isset($this->uninstalledUidList[$uid])) {
                    $this->uninstalledUidList[$uid] = ['snsid' => $snsid, 'uninstallTime' => $uninstallTime];

                    return;
                }
                $savedUninstallTime = $this->uninstalledUidList[$uid]['uninstallTime'];
                if ($uninstallTime > $savedUninstallTime) {
                    $this->uninstalledUidList[$uid]['uninstallTime'] = $uninstallTime;
                }
            },
            $dataSet
        );

        return $this->uninstalledUidList;
    }

    /**
     * @param int[]  $uidList
     * @param int[]  $uninstalledUidList [uid => value, uid => value]
     * @param string $columns
     *
     * @return array[]
     */
    public function readUserInfo(array $uidList, array $uninstalledUidList, $columns = '*')
    {
        $pdo = $this->pdo;
        $placeHolderList = array_pad([], count($uidList), '?');
        $sql = sprintf(
            '/* %s */SELECT %s FROM tbl_user WHERE uid IN (%s)',
            __METHOD__,
            $columns,
            implode(',', $placeHolderList)
        );
        $statement = $pdo->prepare($sql);
        if ($statement === false) {
            throw new \RuntimeException(json_encode($pdo->errorInfo()));
        }
        $statement->execute($uidList);

        $allRows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $resultSet = [];
        foreach ($allRows as $row) {
            $uid = (int) $row['uid'];
            if (array_key_exists($uid, $uninstalledUidList)) {
                $activeLoginTime = (int) $row['logintime'];
                $uninstallTime = $uninstalledUidList[$uid]['uninstallTime'];
                if ($activeLoginTime <= $uninstallTime) {
                    $row['status'] = 0;
                    if ($this->verbose) {
                        appendLog(sprintf('%s(%d) => uninstalled', $row['snsid'], $uid));
                    }
                }
            }
            $resultSet[$uid] = $row;
        }

        return $resultSet;
    }

    /**
     * @param string $date
     *
     * @return array [uid, uid]
     */
    public function listNewUser($date)
    {
        $pdo = $this->pdo;
        if (!(is_string($date) && strlen($date) == strlen('2016-04-04'))) {
            throw new \InvalidArgumentException('date format should be like yyyy-mm-dd');
        }
        $sql = sprintf(
            '/* %s */SELECT uid FROM tbl_user WHERE DATE(addtime)=? AND logintime>=UNIX_TIMESTAMP("2012-01-01")',
            __METHOD__
        );
        $statement = $pdo->prepare($sql);
        $statement->execute([$date]);

        $uidList = $statement->fetchAll(PDO::FETCH_COLUMN);

        return $uidList;
    }

    /**
     * @param int[] $uidList
     *
     * @return array [uid => locale, uid => locale]
     */
    public function listLocale(array $uidList = [])
    {
        $pdo = $this->pdo;

        if ($uidList) {
            $placeHolderList = array_pad([], count($uidList), '?');
            $sql = sprintf(
                '/* %s */SELECT uid,locale FROM tbl_user_locale WHERE uid IN (%s)',
                __METHOD__,
                implode(',', $placeHolderList)
            );
            $statement = $pdo->prepare($sql);
            $statement->execute($uidList);
        } else {
            $sql = sprintf(
                '/* %s */SELECT uid,locale FROM tbl_user_locale',
                __METHOD__
            );
            $statement = $pdo->prepare($sql);
            $statement->execute();
        }

        $resultSet = $statement->fetchAll(PDO::FETCH_ASSOC);

        $pairs = [];
        array_map(
            function (array $user) use (&$pairs) {
                $locale = strtoupper($user['locale']);
                if ($locale !== '-') {
                    $pairs[(int) $user['uid']] = $locale;
                }
            },
            $resultSet
        );

        return $pairs;
    }

    /**
     * @param int $fromTs
     *
     * @return \int[] [uid, uid]
     */
    public function listActiveUser($fromTs)
    {
        $pdo = $this->pdo;
        $sql = sprintf(
            '/* %s */SELECT uid FROM tbl_user_session FORCE INDEX (time_last_active) WHERE time_last_active>?',
            __METHOD__
        );
        $statement = $pdo->prepare($sql);
        $statement->execute([(int) $fromTs]);

        $uidList = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_map(
            function ($uid) {
                return (int) $uid;
            },
            $uidList
        );
    }

    /**
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->dbName;
    }
}
