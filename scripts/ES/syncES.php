<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/03/30
 * Time: 16:58.
 */
use script\Machine;
use script\WorkRoundGenerator;

require __DIR__.'/../../bootstrap.php';
spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

$options = getopt(
    'v',
    [
        'gv:',
        'es:',
        'bs:',
        'interval:',
        'round:',
        'repeatTimes:',
        'waitTime:',
    ]
);
$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$esHost = isset($options['es']) ? $options['es'] : '52.19.73.190';
$backStep = isset($options['bs']) ? $options['bs'] : 1;
$interval = isset($options['interval']) ? $options['interval'] : 20;
$round = isset($options['round']) ? $options['round'] : 100;
$maxRepeatTimes = isset($options['repeatTimes']) ? $options['repeatTimes'] : 100;
$longestWaitTime = isset($options['waitTime']) ? $options['waitTime'] : 10 * 60;

$lastActiveTimestamp = time() - $backStep;
$quitTimestamp = time() + $round * $interval;

if ($verbose) {
    $msg = sprintf(
        'game version: %s, ES host: %s, backStep=%d, interval=%d, round=%d, start at: %s, quit at: %s',
        $gameVersion,
        $esHost,
        $backStep,
        $interval,
        $round,
        date('H:i:s', $lastActiveTimestamp),
        date('H:i:s', $quitTimestamp)
    );
    dump($msg);
    dump(sprintf('max repeat times: %d, max wait time: %d', $maxRepeatTimes, $longestWaitTime));
}

$myself = basename(__FILE__);
$stepGenerator = WorkRoundGenerator::generate($lastActiveTimestamp, $quitTimestamp, $interval, $verbose);
foreach ($stepGenerator as $timestamp) {
    $msg = $myself.': '.date('c', $timestamp).' run with ts '.$timestamp;
    dump($msg);
    appendLog($msg);
    (new Machine($gameVersion, $esHost))->run($maxRepeatTimes, $longestWaitTime);
}
