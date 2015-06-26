<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/26
 * Time: 7:41 PM
 */
require __DIR__ . '/../vendor/autoload.php';

use React\Dns\Resolver\Factory as ResolverFactory;
use React\Dns\Resolver\Resolver;
use Recoil\Recoil;

/**
 * Resolve a domain name and store the result in Redis.
 * @param Resolver $dnsResolver
 * @param string $domainName
 * @return Generator
 */
function resolveAndStore(Resolver $dnsResolver, $domainName)
{
    $ipAddress = (yield $dnsResolver->resolve($domainName));
    echo 'Resolved "' . $domainName . '" to ' . $ipAddress . PHP_EOL;
}

Recoil::run(
    function () {
        $dnsResolver = (new ResolverFactory)->create(
            '8.8.8.8',
            (yield Recoil::eventLoop())
        );

        yield [
            resolveAndStore($dnsResolver, 'recoil.io'),
            resolveAndStore($dnsResolver, 'reactphp.org'),
            resolveAndStore($dnsResolver, 'icecave.com.au'),
            resolveAndStore($dnsResolver, 'funplus.com'),
        ];
    }
);
