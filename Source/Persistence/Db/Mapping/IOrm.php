<?php

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\IEntitySetProvider;
use Dms\Core\Model\Criteria\IRelationPropertyIdTypeProvider;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Schema\Database;

/**
 * The orm interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IOrm extends IRelationPropertyIdTypeProvider
{
    /**
     * Gets all the entity mappers registered in the orm.
     *
     * @return IEntityMapper[]
     */
    public function getEntityMappers();

    /**
     * Returns whether the orm has a mapper for the supplied
     * entity class.
     *
     * If the entity mapper is mapped to multiple tables
     * the table name must be specified.
     *
     * @param string      $entityClass
     * @param string|null $tableName
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function hasEntityMapper($entityClass, $tableName = null);

    /**
     * Gets the entity mapper for the supplied class.
     * If the entity mapper is mapped to multiple tables
     * the table name must be specified.
     *
     * @param string      $entityClass
     * @param string|null $tableName
     *
     * @return IEntityMapper
     * @throws InvalidArgumentException
     */
    public function getEntityMapper($entityClass, $tableName = null);

    /**
     * Gets the entity mapper for the supplied class.
     * If the entity mapper is mapped to multiple tables
     * the table name must be specified.
     *
     * @param string      $entityClass
     * @param string|null $tableName
     *
     * @return IEntityMapper|null
     */
    public function findEntityMapper($entityClass, $tableName = null);

    /**
     * Gets all the embedded object type registered in the orm.
     *
     * @return string[]
     */
    public function getEmbeddedObjectTypes();

    /**
     * Returns whether the orm has a mapper for the supplied object class.
     *
     * @param string $valueObjectClass
     *
     * @return bool
     */
    public function hasEmbeddedObjectMapper($valueObjectClass);

    /**
     * Gets the embedded object mapper for the supplied class.
     *
     * @param IObjectMapper $parentMapper
     * @param string        $valueObjectClass
     *
     * @return IEmbeddedObjectMapper
     * @throws InvalidArgumentException
     */
    public function loadEmbeddedObjectMapper(IObjectMapper $parentMapper, $valueObjectClass);

    /**
     * Gets the orms which have been included in this parent orm.
     *
     * @return IOrm[]
     */
    public function getIncludedOrms();

    /**
     * Gets the database structure for the orm.
     *
     * @return Database
     */
    public function getDatabase();

    /**
     * Loads the entity set provider for the supplied db connection.
     *
     * @param IConnection $connection
     *
     * @return IEntitySetProvider
     */
    public function getEntityDataSourceProvider(IConnection $connection);
}