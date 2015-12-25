<?php

namespace Dms\Core\Persistence;

use Dms\Core\Exception;
use Dms\Core\Exception\NotImplementedException;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Definition\ReadMapperDefinition;
use Dms\Core\Persistence\Db\Mapping\ReadModel\ReadModelMapper;

/**
 * An implementation of the read model repository
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class ReadModelRepository extends DbRepositoryBase implements IReadModelRepository
{
    /**
     * @var ReadModelMapper
     */
    protected $mapper;

    /**
     * @var IObjectMapper
     */
    protected $parentMapper;

    /**
     * DbRepository constructor.
     *
     * @param IConnection $connection
     * @param IOrm        $orm
     */
    public function __construct(IConnection $connection, IOrm $orm)
    {
        $definition = new ReadMapperDefinition($orm);
        $this->define($definition);

        parent::__construct($connection, new ReadModelMapper($definition));

        $this->parentMapper = $definition->getParentMapper();
    }

    /**
     * Defines the structure of the read model.
     *
     * @param ReadMapperDefinition $map
     *
     * @return void
     */
    abstract protected function define(ReadMapperDefinition $map);

    /**
     * {@inheritDoc}
     */
    final public function getReadModelType()
    {
        return $this->mapper->getObjectType();
    }

    /**
     * @return IObjectMapper
     */
    final public function getParentMapper()
    {
        return $this->parentMapper;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->loadCount($this->mapper->getSelect());
    }

    /**
     * {@inheritDoc}
     */
    public function getAll()
    {
        return $this->load($this->mapper->getSelect());
    }

    /**
     * @inheritDoc
     */
    public function contains($object)
    {
        throw NotImplementedException::method(__METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function containsAll(array $objects)
    {
        throw NotImplementedException::method(__METHOD__);
    }
}