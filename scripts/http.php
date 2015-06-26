<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 6:48 PM
 */

require __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('PRC');

$loop = React\EventLoop\Factory::create();

$dnsResolver = (new React\Dns\Resolver\Factory())->createCached('8.8.8.8', $loop);

$client = (new React\HttpClient\Factory())->create($loop, $dnsResolver);

//$request = $client->request('GET', 'http://www.funplus.com');
$request = $client->request('GET', 'https://api.github.com/repos/reactphp/react/commits');
$request->on('response', function (React\HttpClient\Response $response) {
    $buffer = '';

    $response->on('data', function ($data) use (&$buffer) {
        $buffer .= $data;
        echo '.';
    });

    $response->on('end', function () use (&$buffer) {
        $decoded = json_decode($buffer, true);
        $latest  = $decoded[0]['commit'];
        $author  = $latest['author']['name'];
        $date    = date('F j, Y', strtotime($latest['author']['date']));
        echo "\n";
        echo "Latest commit on react was done by {$author} on {$date}\n";
        echo "{$latest['message']}\n";
    });
});

$request->on('end', function ($error, $response) {
    echo $error;
});
$request->end();
$loop->run();
