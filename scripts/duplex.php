<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 2:29 PM
 */

function gen()
{
    $ret = (yield 'yield1');
    echo "[gen] $ret" . PHP_EOL;

    try {
        $ret = (yield 'yield2');
        echo "[gen] $ret" . PHP_EOL;
    } catch (Exception $ex) {
        echo '[gen][Exception]', $ex->getMessage(), "\n";
        yield 'EX';
    }
    echo "[gen]finish\n";
}

$gen = gen();
$ret = $gen->current();
echo '[main]', $ret, "\n";
$ret = $gen->send('send1');
echo '[main]', $ret, "\n";

$ret = $gen->throw(new Exception('HAHA'));
echo '[main]', var_export($ret, true), "\n";
