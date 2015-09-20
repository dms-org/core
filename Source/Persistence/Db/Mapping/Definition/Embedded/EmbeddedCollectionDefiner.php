<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomValueObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;

/**
 * The embedded collection definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedCollectionDefiner extends EmbeddedRelationDefiner
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $primaryKeyName;

    /**
     * @var string
     */
    private $foreignKeyToParentName;

    /**
     * Sets the name of the child table
     *
     * @param string $tableName
     *
     * @return static
     */
    public function toTable($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Sets the primary key name of the child table.
     *
     * @param string $primaryKeyName
     *
     * @return static
     */
    public function withPrimaryKey($primaryKeyName)
    {
        $this->primaryKeyName = $primaryKeyName;

        return $this;
    }

    /**
     * Sets the foreign key column name to map the children
     * to the parent id.
     *
     * @param string $foreignKeyName
     *
     * @return static
     */
    public function withForeignKeyToParentAs($foreignKeyName)
    {
        $this->foreignKeyToParentName = $foreignKeyName;

        return $this;
    }

    /**
     * Sets the mapper to use for mapping the collection.
     *
     * @param IEmbeddedObjectMapper $mapper
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function using(IEmbeddedObjectMapper $mapper)
    {
        $this->defineRelation(function () use ($mapper) {
            return $mapper;
        });
    }

    /**
     * Sets the value object class to use for the collection.
     *
     * @param string $valueObjectClass
     *
     * @return void
     * @throws InvalidOperationException
     */
    public function to($valueObjectClass)
    {
        $this->defineRelation(function (IObjectMapper $parentMapper) use ($valueObjectClass) {
            return $this->orm->loadEmbeddedObjectMapper($parentMapper, $valueObjectClass);
        });
    }

    /**
     * Defines the embedded object mapper using the supplied callback
     *
     * @param callable $mapperDefinitionCallback
     *
     * @return void
     */
    public function usingCustom(callable $mapperDefinitionCallback)
    {
        $this->defineRelation(function (IObjectMapper $parentMapper) use ($mapperDefinitionCallback) {
            return new CustomValueObjectMapper($this->orm, $parentMapper, $mapperDefinitionCallback);
        });
    }

    /**
     * @param callable $mapperLoader
     *
     * @return void
     * @throws InvalidOperationException
     */
    private function defineRelation(callable $mapperLoader)
    {
        if (!$this->tableName || !$this->primaryKeyName || !$this->foreignKeyToParentName) {
            throw new InvalidOperationException(
                    'Must supply table name, primary key name and foreign key name for embedded collection relation'
            );
        }

        call_user_func($this->callback, $mapperLoader, $this->tableName, $this->primaryKeyName, $this->foreignKeyToParentName);
    }
}