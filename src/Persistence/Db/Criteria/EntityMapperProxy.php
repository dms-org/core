<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Type\IType;
use Dms\Core\Persistence\Db\Mapping\Hook\IPersistHook;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Query\Delete;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\Db\Row;
use Dms\Core\Persistence\Db\RowSet;
use Dms\Core\Persistence\Db\Schema\ForeignKey;
use Dms\Core\Persistence\Db\Schema\Table;
use Dms\Core\Persistence\Db\Mapping\ReadOnlyObjectMapperProxy;

/**
 * This class wraps an embedded object mapper as an entity mapper.
 *
 * It can only be used for reading.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityMapperProxy extends ReadOnlyObjectMapperProxy implements IEntityMapper
{
    /**
     * @var IEmbeddedObjectMapper
     */
    protected $mapper;

    /**
     * {@inheritDoc}
     */
    public function __construct(IEmbeddedObjectMapper $mapper)
    {
        if (!$mapper->isSeparateTable()) {
            throw InvalidArgumentException::format('The mapper must map to a separate table');
        }

        parent::__construct($mapper);
    }


    /**
     * @inheritDoc
     */
    public function getCollectionType() : IType
    {
        return $this->mapper->getCollectionType();
    }

    /**
     * @inheritDoc
     */
    public function onUpdatedPrimaryTable(callable $callback)
    {

    }

    /**
     * Gets the table where the primary key of the parent entity is stored.
     *
     * @return Table
     */
    public function getPrimaryTable() : Table
    {
        return $this->mapper->getDefinition()->getTable();
    }

    /**
     * Gets the table name where the primary key of the parent entity is stored.
     *
     * @return string
     */
    public function getPrimaryTableName() : string
    {
        return $this->getPrimaryTable()->getName();
    }

    /**
     * Gets all the tables that store this entity hierarchy.
     *
     * @return Table[]
     */
    public function getTables() : array
    {
        return [$this->mapper->getDefinition()->getTable()];
    }

    /**
     * @return Select
     */
    public function getSelect() : Select
    {
        return $this->getRawSelect();
    }

    /**
     * @inheritDoc
     */
    public function getRawSelect() : Select
    {
        return Select::from($this->getPrimaryTable());
    }

    /**
     * @param Row[] $rows
     *
     * @return RowSet
     */
    public function rowSet(array $rows) : RowSet
    {
        return new RowSet($this->getPrimaryTable(), $rows);
    }

    // NOT REQUIRED

    public function addForeignKey(ForeignKey $foreignKey)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function addPersistHook(IPersistHook $persistHook)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function deleteFromQuery(PersistenceContext $context, Delete $deleteQuery)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persist(PersistenceContext $context, IEntity $entity) : Row
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAll(PersistenceContext $context, array $entities) : array
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function delete(PersistenceContext $context, IEntity $entity)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function deleteAll(PersistenceContext $context, array $ids)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistToRow(PersistenceContext $context, ITypedObject $object, Row $row)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    public function persistAllToRows(PersistenceContext $context, array $objects, array $rows)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}