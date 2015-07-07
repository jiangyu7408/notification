<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/07
 * Time: 5:31 PM
 */

if (function_exists('geoip_country_code_by_name')) {
    function ip2cc($ip)
    {
        return geoip_country_code_by_name($ip);
    }
} else {
    require_once __DIR__ . '/geoip/geoip.php';
    function ip2cc($ip)
    {
        static $gi = null;
        if ($gi === null) {
            $gi = geoip_open(__DIR__ . '/geoip/GeoIP.dat', GEOIP_STANDARD);
        }
        return geoip_country_code_by_addr($gi, $ip);
    }
}
