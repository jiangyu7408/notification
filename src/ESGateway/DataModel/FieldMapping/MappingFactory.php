<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 11:49.
 */
namespace ESGateway\DataModel\FieldMapping;

/**
 * Class MappingFactory.
 */
class MappingFactory
{
    /**
     * @return array
     */
    public function make()
    {
        /** @var AbstractMappingFactory[] $subFactoryList */
        $subFactoryList = [
            new StringMappingFactory(),
            new DateMappingFactory(),
            new NumberMappingFactory(),
        ];

        $mapping = [];
        foreach ($subFactoryList as $subFactory) {
            $mapping = array_merge($mapping, $subFactory->make());
        }

        return ['properties' => $mapping];
    }
}
