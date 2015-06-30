<?php

date_default_timezone_set('PRC');

function newRequest($url)
{
    $host = 'farm-dev3.socialgamenet.com';

    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_HTTPHEADER, ['Host: ' . $host]);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_TIMEOUT, 5);
    curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($request, CURLOPT_HEADER, 0); // no headers in the output

    return $request;
}

function urlProvider($size)
{
    $urlProvider = [];

    $url = 'http://10.0.64.56/index.html';
    for ($i = 0; $i < $size; $i++) {
        $urlProvider[] = $url . '?' . $i;
    }

    return $urlProvider;
}

function nextTask($tasks)
{
    static $taskId = 0;

    if (count($tasks) === 0) {
        trigger_error('empty task queue');
    }

    if ($taskId === 0) {
        $taskId++;
        return $tasks[0];
    }

    if ($taskId > (count($tasks) - 1)) {
        return null;
    }

    $task = $tasks[$taskId];

    $taskId++;

    return $task;
}

function refillQueue($multiHandle, &$concurrentRequests, $url)
{
    $request = newRequest($url);
    $ret     = curl_multi_add_handle($multiHandle, $request);
    assert($ret === 0);
    $concurrentRequests[$url] = $request;
}

/**
 * @param $size
 * @param $concurrencyLevel
 * @param $verbose
 */
function multi($size, $concurrencyLevel, $verbose)
{
//    if ($concurrencyLevel > 250) {
//        $concurrencyLevel = 250;
//        echo 'Rate limit to 250' . PHP_EOL;
//    }
    echo 'size = ' . $size . ', concurrency level = ' . $concurrencyLevel . PHP_EOL;

    $tasks = urlProvider($size);

    $multiHandle = curl_multi_init(); // init the curl Multi

    $concurrentRequests = []; // create an array for the individual curl handles

    for ($i = 0; $i < $concurrencyLevel; $i++) {
        $url = nextTask($tasks);
        if ($url === null) {
            break;
        }

        echo '.';

        $request = newRequest($url);
        curl_multi_add_handle($multiHandle, $request);
        $concurrentRequests[$url] = $request;
    }

    $retry   = 0;
    $fail    = 0;
    $success = 0;
    $running = null;

    $noMoreTasks = false;

    $retryQueue = [];

    do {
        while (($ret = curl_multi_exec($multiHandle, $running)) === CURLM_CALL_MULTI_PERFORM) {
            ;
        }
        if ($ret !== CURLM_OK) {
            break;
        }

        // a request was just completed -- find out which one
        while ($done = curl_multi_info_read($multiHandle)) {
            // on complete event
            $doneHandle = $done['handle'];
            $info       = curl_getinfo($doneHandle);
            $doneUrl    = $info['url'];
            unset($concurrentRequests[$doneUrl]);

            // on complete successfully
            if ($info['http_code'] === 200) {
                $success++;

                $output = curl_multi_getcontent($done['handle']);
                assert(is_string($output));
                if ($verbose) {
                    echo 'response len = ' . strlen($output) . PHP_EOL;
                }

                if ($noMoreTasks || ($url = nextTask($tasks)) === null) {
                    $noMoreTasks = true;
                    continue;
                }

                echo '.';

                // start a new request (it's important to do this before removing the old one)
                refillQueue($multiHandle, $concurrentRequests, $url);
                $running++;

                // remove the curl handle that just completed
                curl_multi_remove_handle($multiHandle, $doneHandle);
//                echo "done url: " . $doneUrl . PHP_EOL;

//                echo 'add new request: ' . $url . PHP_EOL;
//                print_r($concurrentRequests);
            } else {
                // on complete failed
                if (!array_key_exists($doneUrl, $retryQueue)) {
                    $retryQueue[$doneUrl] = 0;
                    echo 'error[' . curl_errno($doneHandle) . '] on ' . $doneUrl
                         . ' => ' . curl_error($doneHandle) . PHP_EOL;
                }
                $retryQueue[$doneUrl]++;

                if ($retryQueue[$doneUrl] < 3) {
                    refillQueue($multiHandle, $concurrentRequests, $doneUrl);
                    $running++;
                    $retry++;
                    curl_multi_remove_handle($multiHandle, $doneHandle);
                    usleep(100000);
                } else {
                    $fail++;
                }
            }
        }
    } while ($running);

    curl_multi_close($multiHandle);

    echo PHP_EOL . "success: {$success}, retry: {$retry}, fail: {$fail}" . PHP_EOL;
}

function single()
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, 'http://www.baidu.com/');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    echo strlen($response) . PHP_EOL;

    curl_close($ch);
}

$options = getopt('s:c:v', []);

$size        = isset($options['s']) ? (int)$options['s'] : 1;
$concurrency = isset($options['c']) ? (int)$options['c'] : 1;

multi($size, $concurrency, array_key_exists('v', $options));
