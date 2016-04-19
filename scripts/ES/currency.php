<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 16:58.
 */

require __DIR__.'/../../bootstrap.php';
spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

$ret = \DataProvider\Currency\CurrencyQuery::query('TWD');
dump($ret);
