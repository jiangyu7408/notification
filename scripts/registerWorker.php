<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/24
 * Time: 7:16 PM
 */
use BusinessEntity\Notif;
use BusinessEntity\NotifFactory;
use Config\RedisQueueConfig;
use Persistency\Storage\RedisClientFactory;
use Queue\RedisQueue;

require __DIR__ . '/../bootstrap.php';

/**
 * @param NotifFactory $factory
 * @param string $registerRequestString
 * @return Notif|null
 */
function makeNotif(NotifFactory $factory, $registerRequestString)
{
    $reqArr = json_decode($registerRequestString, true);
    if (!is_array($reqArr)) {
        // TODO: add error handling
        return null;
    }

    if (!array_key_exists('fired', $reqArr)) {
        $reqArr['fired'] = false;
    }

    $notif = $factory->make($reqArr);
    return $notif;
}

function getRequest(RedisQueueConfig $config)
{
    static $queue = null;
    static $redis;

    if ($queue === null) {
        $redis = (new RedisClientFactory())->create($config);
        $queue = (new RedisQueue($redis, $config->queueName))->setBlockTimeout(5);
    }

    $retryMax = 3;
    $retry    = 0;
    while (true) {
        $request = null;
        try {
            $request = $queue->pop();
        } catch (Predis\Connection\ConnectionException $e) {
            echo time() . ' ' . $e->getMessage() . PHP_EOL;
            $redis->connect();
            $retry++;
            if ($retry > $retryMax) {
                echo time() . ' redis connect retry fail' . PHP_EOL;
                break;
            }
        }
        if ($request) {
            yield $request;
        }
    }
}

function main()
{
    $options = getopt('v');

    $verbose = array_key_exists('v', $options);

    $queueConfig = \Application\Facade::getInstance()->getRegisterQueueConfig();

    $notifRepo = \Application\Facade::getInstance()->getNotifRepo();

    if ($verbose) {
        $dsn = $queueConfig->toString();
        dump('queueName[' . $queueConfig->queueName . '] ' . $dsn . ' timeout=' . $queueConfig->timeout);
    }

    $notifFactory = new NotifFactory();

    foreach (getRequest($queueConfig) as $request) {
        $notif = makeNotif($notifFactory, $request);
        echo '.';
        if (!($notif instanceof Notif)) {
            continue;
        }

        if ($verbose) {
            dump(time() . ' register: ' . json_encode($notif));
        }
        $notifRepo->register($notif);
    }
}

main();
