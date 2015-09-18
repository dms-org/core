<?php

namespace Iddigital\Cms\Core\Persistence\Db\Connection;

use Iddigital\Cms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The db query interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IQuery
{
    /**
     * Gets the query connection.
     *
     * @return IConnection
     */
    public function getConnection();

    /**
     * Sets the query parameter
     *
     * @param int|string $parameter
     * @param mixed      $value
     *
     * @return IQuery
     */
    public function setParameter($parameter, $value);

    /**
     * Sets the query parameters
     *
     * @param array $parameters
     *
     * @return IQuery
     */
    public function setParameters(array $parameters);

    /**
     * Executes the query.
     *
     * @param array $parameters
     *
     * @return IQuery
     */
    public function execute(array $parameters = []);

    /**
     *  Returns whether the query has executed.
     *
     * @return bool
     */
    public function hasExecuted();

    /**
     * Gets the affected rows or null if not applicable
     *
     * @return int|null
     */
    public function getAffectedRows();

    /**
     * Gets the result set of the query or null if not applicable.
     *
     * @return array[]|null
     */
    public function getResults();
}