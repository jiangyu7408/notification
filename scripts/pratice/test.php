<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:29 PM
 */

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/../../bootstrap.php';

$redisConfig = \Application\Facade::getInstance()->getRedisConfig();

dump($redisConfig);

$output = new ConsoleOutput();
$table  = new Table($output);
$table->setHeaders(['ColA', 'ColB']);
$table->setRows([['aaa', 'bbb']]);
$table->render();

$progress = new ProgressBar($output, 50);

// start and displays the progress bar
$progress->start();

$i = 0;
while ($i++ < 50) {
    // ... do some work
    time_nanosleep(0, 1 * 1e8);

    // advance the progress bar 1 unit
    $progress->advance();

    // you can also advance the progress bar by more than 1 unit
    // $progress->advance(3);
}

// ensure that the progress bar is at 100%
$progress->finish();
