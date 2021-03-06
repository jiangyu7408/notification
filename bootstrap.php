<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/17
 * Time: 2:13 PM.
 */
error_reporting(E_ALL);
ini_set('memory_limit', '4G');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('assert.active', '1');
ini_set('assert.warning', '1');
ini_set('assert.bail', '1');

define('ELASTIC_SEARCH_HOST', '52.19.73.190');
define('ELASTIC_SEARCH_PORT', 9200);
define('ELASTIC_SEARCH_SCHEMA_VERSION', 3);
define('ELASTIC_SEARCH_INDEX', 'farm_v'.ELASTIC_SEARCH_SCHEMA_VERSION);

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/library/dump.php';

define('CONFIG_DIR', __DIR__);
$includePath = __DIR__.'/src';
set_include_path(get_include_path().PATH_SEPARATOR.$includePath);

$classLoader = new \Symfony\Component\ClassLoader\ClassLoader();
$classLoader->addPrefixes(
    [
        'src',
    ]
);
$classLoader->register();
