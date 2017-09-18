<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Relation;

use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\Schema\Column;
use Dms\Core\Persistence\Db\Schema\Table;

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
     * @var IType
     */
    protected $valueType;

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
     * @param IType         $valueType
     * @param IObjectMapper $mapper
     * @param string        $dependencyMode
     * @param Table[]       $relationshipTables
     * @param Column[]      $parentColumnsToLoad
     */
    public function __construct(
            string $idString,
            IType $valueType,
            IObjectMapper $mapper,
            string $dependencyMode,
            array $relationshipTables,
            array $parentColumnsToLoad
    ) {
        $this->idString            = $idString;
        $this->valueType           = $valueType;
        $this->mapper              = $mapper;
        $this->dependencyMode      = $dependencyMode;
        $this->relationshipTables  = $relationshipTables;
        $this->parentColumnsToLoad = array_unique($parentColumnsToLoad, SORT_STRING);
    }

    /**
     * @return string
     */
    public function getIdString() : string
    {
        return $this->idString;
    }

    /**
     * @inheritDoc
     */
    public function getValueType() : IType
    {
        return $this->valueType;
    }

    /**
     * @return IObjectMapper
     */
    final public function getMapper() : IObjectMapper
    {
        return $this->mapper;
    }

    /**
     * @return string
     */
    final public function getDependencyMode() : string
    {
        return $this->dependencyMode;
    }

    /**
     * @return Table[]
     */
    final public function getRelationshipTables() : array
    {
        return $this->relationshipTables;
    }

    /**
     * @inheritDoc
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
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
    final public function getParentColumnsToLoad() : array
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
    final protected function setForeignKey(array $rows, string $foreignKey, $value)
    {
        foreach ($rows as $row) {
            $row->setColumn($foreignKey, $value);
        }
    }

    /**
     * @param callable $callback
     *
     * @return mixed
     */
    final protected function disableLazyLoadingFor(callable $callback)
    {
        $orm             = $this->mapper->getDefinition()->getOrm();
        $originalSetting = $orm->isLazyLoadingEnabled();

        try {
            $orm->enableLazyLoading(false);
            return $callback();
        } finally {
            $orm->enableLazyLoading($originalSetting);
        }
    }
}