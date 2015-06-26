<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 4:29 PM
 */

require __DIR__ . '/../vendor/autoload.php';

use React\Dns\Resolver\Factory as ResolverFactory;
use React\HttpClient\Client;
use Recoil\Recoil;

date_default_timezone_set('PRC');

/**
 * @param Client $client
 * @param string $url
 * @return Generator
 */
function fetchHtml(Client $client, $url)
{
    /** @var \React\Http\Response $request */
    $request = $client->request('GET', $url);
    $request->on('response', function (React\HttpClient\Response $response) {
        $buffer = '';

        $response->on('data', function ($data) use (&$buffer) {
            $buffer .= $data;
        });

        $response->on('end', function () use (&$buffer, $response) {
            echo $response->getCode() . ': ' . strlen($buffer) . PHP_EOL;
        });
    });

    $request->on('end', function ($error) {
        echo $error;
    });
    $request->end();
}

$options = getopt('', array('size:'));

$size = array_key_exists('size', $options) ? (int)$options['size'] : 1;

Recoil::run(
    function () use ($size) {

        $eventLoop   = (yield Recoil::eventLoop());
        $dnsResolver = (new ResolverFactory)->create(
            '8.8.8.8',
            $eventLoop
        );

        $httpClient = (new React\HttpClient\Factory())->create($eventLoop, $dnsResolver);

        $requests = [];
        for ($i = 0; $i < $size; $i++) {
            $requests[] = fetchHtml($httpClient, 'http://wiki.ifunplus.cn/');
        }

        yield $requests;
    }
);
