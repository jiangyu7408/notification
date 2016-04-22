<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/22
 * Time: 16:11.
 */
use Elastica\Type\Mapping;
use ESGateway\DataModel\FieldMapping\MappingFactory;

require __DIR__.'/../../bootstrap.php';

$options = getopt('v', ['gv:']);
$verbose = isset($options['v']);

$gameVersion = null;
if (defined('GAME_VERSION')) {
    $gameVersion = GAME_VERSION;
} else {
    assert(isset($options['gv']), 'game version not defined');
    $gameVersion = trim($options['gv']);
}

$indexName = ELASTIC_SEARCH_INDEX;

$clientFactory = new \ESGateway\Factory();
$client = $clientFactory->makeClient(ELASTIC_SEARCH_HOST, ELASTIC_SEARCH_PORT);

$index = $client->getIndex($indexName);
$exists = $index->exists();
if (!$exists) {
    $response = $index->create();
    dump($response);
}

$type = $index->getType('user:'.$gameVersion);

$currentMapping = $type->getMapping();
dump('current mapping:');
dump($currentMapping);
if (!$currentMapping) {
    $mapping = new Mapping();
    $mapping->setType($type);

    $mappingProperties = (new MappingFactory())->make();
    $mapping->setProperties($mappingProperties);
    $response = $mapping->send();
    dump($response);
}
