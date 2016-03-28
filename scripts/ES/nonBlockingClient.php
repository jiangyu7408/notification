<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/28
 * Time: 14:52.
 */
require __DIR__.'/../../bootstrap.php';

/**
 * @param string $url
 *
 * @return string
 */
function parseSnsid($url)
{
    $arr = explode('/', parse_url($url, PHP_URL_PATH));

    return (string) $arr[3];
}

$snsidList = [
    '100001349218797',
    '675097095878591',
    '675097095878592',
    '675097095878593',
    '675097095878594',
];

$taskFactory = new \Worker\Model\TaskFactory();
$tasks = array_map(function ($snsid) use ($taskFactory) {
    $url = sprintf('http://52.19.73.190:9200/farm/user:tw/%s/_update', $snsid);
    $postData = sprintf('{"doc":{"status":"%d"}}', time());

    return $taskFactory->create($url, [
        CURLOPT_URL => $url,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => 1,
    ]);
}, $snsidList);
//dump($tasks);

PHP_Timer::start();
$curlWorker = new \Worker\CurlWorker(new \Worker\Queue\HttpTracer());
$curlWorker->addTasks($tasks);
$responseList = [];

$trace = $curlWorker->run($responseList, function () {
    echo microtime(true).' got response'.PHP_EOL;
});
$taskWallTime = PHP_Timer::stop();

array_walk($responseList, function (array $response) {
    dump(
        [
            'url' => $response['url'],
            'http_code' => $response['http_code'],
            'content' => $response['content'],
        ]
    );
});

$httpCost = 0;
array_walk($trace, function (\Worker\Queue\HttpTracer $httpTracer, $url) use (&$httpCost) {
    $httpCost += $httpTracer->getElapsedTime();
    dump(parseSnsid($url).' => '.$httpTracer);
});

dump('time cost on http: '.PHP_Timer::secondsToTimeString($httpCost));
dump('wall time on http: '.PHP_Timer::secondsToTimeString($taskWallTime));
dump('Run time: '.PHP_Timer::timeSinceStartOfRequest());
dump(sprintf('Memory: %4.2fMb', memory_get_peak_usage(true) / 1048576));
