<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 4:41 PM
 */

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

if (isset($_SERVER['MODE']) && $_SERVER['MODE'] === 'background') {
    define('BACKGROUND', true);
}

if (!function_exists('appendLog')) {
    if (!defined('BACKGROUND')) {
        function appendLog($var)
        {
            foreach (func_get_args() as $var) {
                VarDumper::dump($var);
            }
        }
    } else {
        function appendLog($var)
        {
            static $handler = null;

            if ($handler === null) {
                $logDir = __DIR__ . '/../log/';
                assert(is_dir($logDir), 'log dir not exist: ' . $logDir);

                $logFile = $logDir . '/' . date('Ymd');

                $cloner  = new VarCloner();
                $dumper  = new CliDumper($logFile);
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
