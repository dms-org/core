<?php

namespace Iddigital\Cms\Core\Persistence\Db\Schema;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The database class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Database
{
    /**
     * @var Table[]
     */
    private $tables = [];

    /**
     * Database constructor.
     *
     * @param Table[]     $tables
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $tables)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'tables', $tables, Table::class);

        foreach ($tables as $table) {
            if (isset($this->tables[$table->getName()])) {
                throw InvalidArgumentException::format(
                        'Could not construct %s: duplicate tables found for name %s',
                        get_class($this), $table->getName()
                );
            }

            $this->tables[$table->getName()] = $table;
        }
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @return string[]
     */
    public function getTableNames()
    {
        return array_keys($this->tables);
    }

    /**
     * @param string $name
     *
     * @return Table
     */
    public function hasTable($name)
    {
        return isset($this->tables[$name]);
    }

    /**
     * @param string $name
     *
     * @return Table
     * @throws InvalidArgumentException
     */
    public function getTable($name)
    {
        if (!$this->hasTable($name)) {
            throw InvalidArgumentException::format(
                    'Could not find table: expecting one of (%s), %s given',
                    Debug::formatValues($this->getTableNames()), $name
            );
        }

        return $this->tables[$name];
    }
}