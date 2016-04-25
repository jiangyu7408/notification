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

$createMappingHandler = function (\Elastica\Type $type) {
    $mapping = new Mapping();
    $mapping->setType($type);

    $mappingProperties = (new MappingFactory())->make();
    $mapping->setProperties($mappingProperties);
    $response = $mapping->send();
    dump('create default mapping');
    dump($response);
};

$options = getopt('', ['gv:', 'index:', 'createIndex:']);

$client = new \Elastica\Client(['host' => ELASTIC_SEARCH_HOST, 'port' => ELASTIC_SEARCH_PORT]);
if (isset($options['createIndex'])) {
    $indexName = trim($options['createIndex']);
    assert(is_string($indexName) && strlen($indexName) > 0 && strpos($indexName, 'farm_v') === 0, 'bad index name');
    $index = $client->getIndex($indexName);
    $exists = $index->exists();
    if (!$exists) {
        $response = $index->create();
        dump('create index');
        dump($response);
    }
    $type = $index->getType('_default_');
    call_user_func($createMappingHandler, $type);

    $gameVersionList = ['tw', 'th', 'us', 'de', 'fr', 'it', 'pl', 'nl', 'br'];
    foreach ($gameVersionList as $gameVersion) {
        $type = $index->getType('user:'.$gameVersion);
        $currentMapping = $type->getMapping();
        dump('current mapping:');
        dump($currentMapping);
        if (!$currentMapping) {
            call_user_func($createMappingHandler, $type);
        }
    }
}

if (isset($options['gv'])) {
    $gameVersion = trim($options['gv']);

    assert(isset($options['index']), 'index name not found');
    $indexName = $options['index'];
    $index = $client->getIndex($indexName);
    $type = $index->getType('user:'.$gameVersion);
    $currentMapping = $type->getMapping();
    dump('current mapping:');
    dump($currentMapping);
    if (!$currentMapping) {
        call_user_func($createMappingHandler, $type);
    }
}
