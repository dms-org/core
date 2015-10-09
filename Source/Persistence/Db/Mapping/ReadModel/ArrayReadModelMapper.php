<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\NestedProperty;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;

/**
 * The array read model mapper class.
 *
 * This can load read models in the form of arrays.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayReadModelMapper extends ReadModelMapper
{


    /**
     * ArrayReadModelMapper constructor.
     *
     * @param IOrm          $orm
     * @param IObjectMapper $fromMapper
     * @param string[]      $indexPropertyNameMap
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IOrm $orm, IObjectMapper $fromMapper, array $indexPropertyNameMap)
    {
        $class                  = $fromMapper->getDefinition()->getClass();
        $indexNestedPropertyMap = [];

        foreach ($indexPropertyNameMap as $index => $propertyName) {
            $indexNestedPropertyMap[$index] = NestedProperty::parsePropertyName($class, $propertyName);
        }

        $nestedPropertyTree = $this->createNestedPropertyTree($indexNestedPropertyMap);

        $definition = new ReadMapperDefinition($orm);
        $definition->from($fromMapper);
        $this->loadDefinitionFromNestedProperties($definition, $nestedPropertyTree);

        parent::__construct($definition);
    }

    /**
     * @param ReadMapperDefinition $definition
     * @param string[]|array[]     $nestedPropertyTree
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function loadDefinitionFromNestedProperties(ReadMapperDefinition $definition, array $nestedPropertyTree)
    {
        $definition->type(ArrayReadModel::class);
        $fromDefinition = $definition->getParentMapper()->getDefinition();

        $propertyColumnMap = $fromDefinition->getPropertyColumnMap();
        $relations         = $fromDefinition->getRelations();

        foreach ($nestedPropertyTree as $propertyName => $indexes) {
            if (isset($propertyColumnMap[$propertyName])) {
                foreach ($indexes as $nestedPropertyName => $index) {
                    if (is_int($nestedPropertyName)) {
                        $definition->properties([$propertyName => $index]);
                    } else {
                        throw InvalidArgumentException::format(
                                'Invalid property for %s: property cannot have nested properties on property mapped to a column',
                                $fromDefinition->getClassName()
                        );
                    }
                }
            } elseif (isset($relations[$propertyName])) {
                foreach ($indexes as $nestedPropertyName => $index) {
                    if (is_int($nestedPropertyName)) {
                        $definition->properties([$propertyName => $index]);
                    } else {
                        $definition
                                ->relation($propertyName)
                                ->to($propertyName)
                                ->load(function (ReadMapperDefinition $definition) use ($index) {
                                    $this->loadDefinitionFromNestedProperties($definition, $index);
                                });
                    }
                }
            } else {
                throw InvalidArgumentException::format(
                        'Invalid property for %s: property %s is not a mapped property',
                        $fromDefinition->getClassName(), $propertyName
                );
            }
        }
    }

    /**
     * @param NestedProperty[] $indexNestedPropertyMap
     *
     * @return array[]
     */
    private function createNestedPropertyTree(array $indexNestedPropertyMap)
    {
        $propertyTree = [];

        foreach ($indexNestedPropertyMap as $index => $nestedProperty) {
            $node =& $propertyTree;

            foreach ($nestedProperty->getNestedProperties() as $property) {
                $node =& $node[$property->getName()];
            }

            $node[] = $index;
        }

        return $propertyTree;
    }
}