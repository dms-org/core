<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Persistence\Db\Schema\Database;


/**
 * The orm interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IOrm
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
}