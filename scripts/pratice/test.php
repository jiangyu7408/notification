<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:29 PM
 */

require __DIR__ . '/../../bootstrap.php';

$facebook = \Application\Facade::getInstance()->getParam('facebook');
dump($facebook);
