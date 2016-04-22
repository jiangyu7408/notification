<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/18
 * Time: 17:48.
 */
namespace Facade\ES;

use ESGateway\Factory;
use Repository\ESGatewayUserRepo;

/**
 * Class Indexer.
 */
class Indexer
{
    /** @var ESGatewayUserRepo */
    protected $repo;
    /** @var Factory */
    protected $userDataFactory;
    /** @var int */
    protected $batchSize;
    /** @var array */
    protected $batchResult = [];
    /** @var \ESGateway\User[] */
    protected $lastRoundData;

    /**
     * Indexer constructor.
     *
     * @param ESGatewayUserRepo $repo
     * @param int               $batchSize
     */
    protected function __construct(ESGatewayUserRepo $repo, $batchSize = 200)
    {
        $this->repo = $repo;
        $this->userDataFactory = $this->repo->getFactory();
        $this->batchSize = $batchSize;
    }

    /**
     * @param array $users
     *
     * @return float[]
     */
    public function batchUpdate(array $users)
    {
        $count = count($users);
        if ($count === 0) {
            return [];
        }

        $esUserList = $this->sanitizeData($users);
        $this->lastRoundData = $esUserList;

        $deltaList = [];

        while (($batch = array_splice($esUserList, 0, $this->batchSize))) {
            $start = microtime(true);
            $errorString = '';
            $success = $this->repo->burst($batch, $errorString);
            if (!$success) {
                $this->batchResult[] = $errorString;
            }
            $deltaList[] = microtime(true) - $start;
        }

        return $deltaList;
    }

    /**
     * @return \ESGateway\User[]
     */
    public function getLastRoundData()
    {
        return $this->lastRoundData;
    }

    /**
     * @return array
     */
    public function getBatchResult()
    {
        return $this->batchResult;
    }

    /**
     * @param array $users
     *
     * @return \ESGateway\User[]
     */
    private function sanitizeData(array $users)
    {
        return array_map(
            function (array $eachUser) {
                return $this->userDataFactory->makeUser($eachUser);
            },
            $users
        );
    }
}
