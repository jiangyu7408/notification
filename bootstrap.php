<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/17
 * Time: 2:13 PM
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('assert.active', '1');
ini_set('assert.warning', '1');
ini_set('assert.bail', '1');

require __DIR__ . '/vendor/autoload.php';

define('CONFIG_DIR', __DIR__);

if (function_exists('geoip_country_code_by_name')) {
    function ip2cc($ip)
    {
        return geoip_country_code_by_name($ip);
    }
} else {
    require_once __DIR__ . '/library/geoip/geoip.php';
    function ip2cc($ip)
    {
        static $gi = null;
        if ($gi === null) {
            $gi = geoip_open(__DIR__ . '/library/geoip/GeoIP.dat', GEOIP_STANDARD);
        }
        return geoip_country_code_by_addr($gi, $ip);
    }
}
