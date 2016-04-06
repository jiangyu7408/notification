<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/06
 * Time: 11:20.
 */
require __DIR__.'/../../bootstrap.php';

spl_autoload_register(
    function ($className) {
        $classFile = str_replace('\\', '/', $className).'.php';
        require $classFile;
    }
);

$serverInfoParser = function ($serverInfo) {
    $arr = explode('  ', $serverInfo);
    $result = [];
    array_walk(
        $arr,
        function ($info) use (&$result) {
            list ($key, $value) = explode(':', $info);
            $result[trim($key)] = (float) $value;
        }
    );

    return $result;
};

$connectionStatusParser = function ($status) {
    $delimiter = strpos($status, ' ');
    $host = substr($status, 0, $delimiter);
    $extra = trim(substr($status, $delimiter));

    return ['host' => $host, 'extra' => $extra];
};

$optionsReader = function (PDO $pdo, array $attributes) use ($serverInfoParser, $connectionStatusParser) {
    $info = [];
    foreach ($attributes as $attribute) {
        $name = 'PDO::ATTR_'.$attribute;
        $value = null;
        try {
            $value = $pdo->getAttribute(constant($name));
            if ($name === 'PDO::ATTR_SERVER_INFO') {
                $value = call_user_func($serverInfoParser, $value);
            }
            if ($name === 'PDO::ATTR_CONNECTION_STATUS') {
                $value = call_user_func($connectionStatusParser, $value);
            }
        } catch (PDOException $e) {
            $value = 'not supported';
            $errMsg = $e->getMessage();
            if (strpos($errMsg, 'not support') === false) {
                $value .= ''.$errMsg;
            }
        }
        $info[$name] = $value;
    }

    return $info;
};

$pdoOptionChecker = function (PDO $pdo) use ($optionsReader) {
    $attributes = [
        'SERVER_INFO',
        //        'SERVER_VERSION',
        //        'CLIENT_VERSION',
        'CONNECTION_STATUS',
        //        'DRIVER_NAME',
        //        'AUTOCOMMIT',
        //        'CASE',
        //        'ERRMODE',
        //        'ORACLE_NULLS',
        //        'PERSISTENT',
        //        'PREFETCH',
        //        'TIMEOUT',
    ];

    return call_user_func($optionsReader, $pdo, $attributes);
};

$gameVersion = 'tw';

$shardConfigList = \script\ShardHelper::shardConfigGenerator($gameVersion);
$infoList = [];
foreach ($shardConfigList as $options) {
    $pdo = \script\ShardHelper::pdoFactory($options);
    $infoList[$options['shardId']] = call_user_func($pdoOptionChecker, $pdo);
}

dump($infoList);
