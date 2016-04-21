<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/21
 * Time: 12:18.
 */
namespace ESGateway\DataModel\FieldMapping;

/**
 * Class AbstractMappingFactory.
 */
abstract class AbstractMappingFactory
{
    /** @var array ['category' => [field1, field2]] */
    protected $listString = [];

    /**
     * @return array
     */
    public function make()
    {
        $handlers = $this->listHandlers();

        $mapping = [];
        foreach ($this->listString as $category => $fieldList) {
            foreach ($handlers as $handler) {
                $ret = call_user_func($handler, $category, $fieldList);
                if ($ret === false) {
                    continue;
                }
                $mapping = array_merge($mapping, $ret);
            }
        }

        return $mapping;
    }

    /**
     * @param string               $expectedCategory
     * @param AbstractFieldMapping $mapping
     * @param string               $category
     * @param array                $fieldList
     *
     * @return array|bool
     */
    protected function onCategory(
        $expectedCategory,
        AbstractFieldMapping $mapping,
        $category,
        array $fieldList
    ) {
        if ($category !== $expectedCategory) {
            return false;
        }

        $mappingArray = $mapping->toArray();

        $list = [];
        foreach ($fieldList as $field) {
            $list[$field] = $mappingArray;
        }

        return $list;
    }

    /**
     * @return \Closure[]
     */
    abstract protected function listHandlers();
}
