<?php

date_default_timezone_set('PRC');

function newRequest($url) {
    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, $url);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($request, CURLOPT_HEADER, 0); // no headers in the output

    return $request;
}

function urlProvider($size) {
    $urlProvider = array();

    $url = 'http://farm-dev3.socialgamenet.com/index.html';
    for ($i = 0; $i < $size; $i++) {
        $urlProvider[] = $url . '?' . $i;
    }

    return $urlProvider;
}

function nextTask($tasks) {
    static $taskId = 0;

    if (count($tasks) === 0) {
        trigger_error('empty task queue');
    }

    if ($taskId === 0) {
        $taskId++;
        return $tasks[0];
    }

    if ($taskId > (count($tasks) - 1)) {
//        echo 'no more tasks' . PHP_EOL;
        return null;
    }

    $task = $tasks[$taskId];

    $taskId++;

    return $task;
}

function refillQueue($multiHandle, &$concurrentRequests, $url) {
    $request = newRequest($url);
    $ret     = curl_multi_add_handle($multiHandle, $request);
    assert($ret === 0);
    $concurrentRequests[$url] = $request;
}

function multi($size, $concurrencyLevel) {
    echo 'size = ' . $size . ', concurrency level = ' . $concurrencyLevel . PHP_EOL;

    $tasks = urlProvider($size);
//    echo 'tasks: '; print_r($tasks);

    $multiHandle = curl_multi_init(); // init the curl Multi

    $concurrentRequests = array(); // create an array for the individual curl handles

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

//    print_r($concurrentRequests);

    $fail    = 0;
    $success = 0;
    $running = null;

    $stats = array();

    $noMoreTasks = false;

    do {
        while (($ret = curl_multi_exec($multiHandle, $running)) === CURLM_CALL_MULTI_PERFORM) {
            ;
        }
        if ($ret != CURLM_OK) {
            break;
        }

        // a request was just completed -- find out which one
        while ($done = curl_multi_info_read($multiHandle)) {
            $doneHandle = $done['handle'];
//            print_r($done);
            $info = curl_getinfo($doneHandle);
//            print_r($info);
            $stats[$info['url']] = $info['total_time'];
            if ($info['http_code'] == 200) {
                $doneUrl = $info['url'];
                $success++;

                $output = curl_multi_getcontent($done['handle']);
                assert(is_string($output));
//                echo "response len = " . strlen($output) . PHP_EOL;

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
                unset($concurrentRequests[$doneUrl]);

//                echo 'add new request: ' . $url . PHP_EOL;
//                print_r($concurrentRequests);

                continue;
            }

            // request failed.  add error handling.
            echo 'error: ' . curl_error($doneHandle) . PHP_EOL;
            break 2;
            $fail++;
        }
    } while ($running);

    curl_multi_close($multiHandle);

    echo PHP_EOL . "success: {$success}, fail: {$fail}" . PHP_EOL;
//    print_r($stats);
}

function single() {
    $ch = curl_init();

// set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "http://www.baidu.com/");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// grab URL and pass it to the browser
    $response = curl_exec($ch);
    echo strlen($response) . PHP_EOL;

// close cURL resource, and free up system resources
    curl_close($ch);
}

$options = getopt('s:c:', array());

$size        = isset($options['s']) ? (int)$options['s'] : 1;
$concurrency = isset($options['c']) ? (int)$options['c'] : 1;

multi($size, $concurrency);
