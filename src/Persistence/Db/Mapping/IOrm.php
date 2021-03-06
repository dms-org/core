<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\IEntitySetProvider;
use Dms\Core\Model\Criteria\IRelationPropertyIdTypeProvider;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\Plugin\IOrmPlugin;
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
    public function getEntityMappers() : array;

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
    public function hasEntityMapper(string $entityClass, string $tableName = null) : bool;

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
    public function getEntityMapper(string $entityClass, string $tableName = null) : IEntityMapper;

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
    public function findEntityMapper(string $entityClass, string $tableName = null);

    /**
     * Gets all the embedded object type registered in the orm.
     *
     * @return string[]
     */
    public function getEmbeddedObjectTypes() : array;

    /**
     * Returns whether the orm has a mapper for the supplied object class.
     *
     * @param string $valueObjectClass
     *
     * @return bool
     */
    public function hasEmbeddedObjectMapper(string $valueObjectClass) : bool;

    /**
     * Gets the embedded object mapper for the supplied class.
     *
     * @param IObjectMapper $parentMapper
     * @param string        $valueObjectClass
     *
     * @return IEmbeddedObjectMapper
     * @throws InvalidArgumentException
     */
    public function loadEmbeddedObjectMapper(IObjectMapper $parentMapper, string $valueObjectClass) : IEmbeddedObjectMapper;

    /**
     * Gets the orms which have been included in this parent orm.
     *
     * @return IOrm[]
     */
    public function getIncludedOrms() : array;

    /**
     * Gets the database structure for the orm.
     *
     * @return Database
     */
    public function getDatabase() : Database;

    /**
     * Loads the entity set provider for the supplied db connection.
     *
     * @param IConnection $connection
     *
     * @return IEntitySetProvider
     */
    public function getEntityDataSourceProvider(IConnection $connection) : IEntitySetProvider;

    /**
     * Gets the current namespace of the orm.
     *
     * This is the prefix of table / constraint names.
     *
     * @return string
     */
    public function getNamespace() : string;

    /**
     * Gets the orm plugins
     *
     * @return IOrmPlugin[]
     */
    public function getPlugins() : array;

    /**
     * Returns an orm with the supplied namespace and plugins.
     *
     * @param string       $namespacePrefix
     * @param IOrmPlugin[] $plugins
     *
     * @return IOrm
     */
    public function update(string $namespacePrefix, array $plugins) : IOrm;

    /**
     * Returns an equivalent orm with the table names and constraint names
     * prefixed by the supplied string.
     *
     * @param string $prefix
     *
     * @return IOrm
     */
    public function inNamespace(string $prefix) : IOrm;

    /**
     * Returns whether lazy loading has been enabled.
     *
     * @return bool
     */
    public function isLazyLoadingEnabled() : bool;

    /**
     * Sets whether lazy-loading of relations should be enabled within the orm.
     *
     * @param bool $flag
     *
     * @return void
     */
    public function enableLazyLoading(bool $flag = true);
}