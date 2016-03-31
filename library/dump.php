<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 4:41 PM.
 */
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

if (isset($_SERVER['MODE']) && $_SERVER['MODE'] === 'background') {
    define('BACKGROUND', true);
    if (!isset($_SERVER['GV'])) {
        dump('background mode must have GV setting');
        die;
    }
    $gameVersion = strtolower(trim($_SERVER['GV']));
    /* @var string $gameVersion */
    assert(strlen($gameVersion) === 2, 'bad game version: '.$gameVersion);
    define('GAME_VERSION', $gameVersion);
}

define('LOG_DIR', realpath(__DIR__.'/../log/'));
define('UID_QUEUE_DIR', realpath(__DIR__.'/../log/queue'));

//dump(get_defined_constants(true)['user']);

if (!function_exists('appendLog')) {
    if (!defined('BACKGROUND')) {
        /**
         * @param mixed $var
         */
        function appendLog($var)
        {
            foreach (func_get_args() as $var) {
                VarDumper::dump($var);
            }
        }
    }

    if (defined('BACKGROUND')) {
        /**
         * @param mixed $var
         */
        function appendLog($var)
        {
            static $handler = null;

            if ($handler === null) {
                $logDir = LOG_DIR;
                assert(is_dir($logDir), 'log dir "'.$logDir.'" must be a dir');
                $logDir .= '/'.date('Ymd');
                if (!is_dir($logDir)) {
                    $success = mkdir($logDir);
                    assert(
                        $success,
                        'log dir create failed: '.$logDir.': '.json_encode(error_get_last())
                    );
                }

                $logFile = $logDir.'/'.GAME_VERSION;

                $cloner = new VarCloner();
                $dumper = new CliDumper($logFile);
                $handler = function ($var) use ($cloner, $dumper) {
                    $dumper->dump($cloner->cloneVar($var));
                };
            }
            $origHandler = VarDumper::setHandler($handler);

            VarDumper::dump(date('H:i:s'));
            foreach (func_get_args() as $var) {
                VarDumper::dump($var);
            }
            VarDumper::setHandler($origHandler);
        }
    }
}
