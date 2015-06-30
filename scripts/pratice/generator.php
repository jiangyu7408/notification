<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 2:21 PM
 */

/**
 * @param int $start
 * @param int $end
 * @param int $step
 * @return Generator
 */
function generator($start, $end, $step = 1)
{
    for ($i = $start; $i <= $end; $i += $step) {
        yield 'name' . $i;
    }
}

$coroutine = generator(1, 2);
xdebug_debug_zval('coroutine');

$valid = $coroutine->valid();
xdebug_debug_zval('valid');

$coroutine->rewind();

$current = $coroutine->current();
xdebug_debug_zval('current');

$valid = $coroutine->valid();
xdebug_debug_zval('valid');

$coroutine->next();
$current2 = $coroutine->current();
xdebug_debug_zval('current2');

$valid = $coroutine->valid();
xdebug_debug_zval('valid');

$coroutine->next();
$current3 = $coroutine->current();
xdebug_debug_zval('current3');

$valid = $coroutine->valid();
xdebug_debug_zval('valid');
