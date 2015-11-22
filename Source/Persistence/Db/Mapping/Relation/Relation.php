<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Relation;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Row;
use Iddigital\Cms\Core\Persistence\Db\Schema\Column;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The relation base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Relation implements IRelation
{
    /**
     * @var string
     */
    protected $idString;

    /**
     * @var IObjectMapper
     */
    protected $mapper;

    /**
     * @var string
     */
    protected $dependencyMode;

    /**
     * @var Table[]
     */
    protected $relationshipTables;

    /**
     * @var string[]
     */
    protected $parentColumnsToLoad;

    /**
     * Relation constructor.
     *
     * @param string        $idString
     * @param IObjectMapper $mapper
     * @param string        $dependencyMode
     * @param Table[]       $relationshipTables
     * @param Column[]      $parentColumnsToLoad
     */
    public function __construct($idString, IObjectMapper $mapper, $dependencyMode, array $relationshipTables, array $parentColumnsToLoad)
    {
        $this->idString            = $idString;
        $this->mapper              = $mapper;
        $this->dependencyMode      = $dependencyMode;
        $this->relationshipTables  = $relationshipTables;
        $this->parentColumnsToLoad = array_unique($parentColumnsToLoad, SORT_STRING);
    }

    /**
     * @return string
     */
    public function getIdString()
    {
        return $this->idString;
    }

    /**
     * @return IObjectMapper
     */
    final public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return string
     */
    final public function getDependencyMode()
    {
        return $this->dependencyMode;
    }

    /**
     * @return Table[]
     */
    final public function getRelationshipTables()
    {
        return $this->relationshipTables;
    }

    /**
     * @inheritDoc
     */
    public function withEmbeddedColumnsPrefixedBy($prefix)
    {
        $clone = clone $this;

        foreach ($clone->parentColumnsToLoad as $key => $parentColumnToLoad) {
            $clone->parentColumnsToLoad[$key] = $prefix . $parentColumnToLoad;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    final public function getParentColumnsToLoad()
    {
        return $this->parentColumnsToLoad;
    }

    /**
     * @param Row[]  $rows
     * @param string $foreignKey
     * @param mixed  $value
     *
     * @return void
     */
    final protected function setForeignKey(array $rows, $foreignKey, $value)
    {
        foreach ($rows as $row) {
            $row->setColumn($foreignKey, $value);
        }
    }
}