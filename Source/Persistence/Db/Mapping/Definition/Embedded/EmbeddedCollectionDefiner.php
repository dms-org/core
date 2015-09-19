<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Iddigital\Cms\Core\Exception\InvalidOperationException;
use Iddigital\Cms\Core\Persistence\Db\Mapping\CustomValueObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * The embedded collection definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EmbeddedCollectionDefiner
{
    /**
     * @var callable
     */
    private $callback;

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

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

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
        if (!$this->tableName || !$this->primaryKeyName || !$this->foreignKeyToParentName) {
            throw new InvalidOperationException(
                'Must supply table name, primary key name and foreign key name for embedded collection relation'
            );
        }

        call_user_func($this->callback, $mapper, $this->tableName, $this->primaryKeyName, $this->foreignKeyToParentName);
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
        $this->using(new CustomValueObjectMapper($mapperDefinitionCallback));
    }
}