<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Util\Debug;

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
    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @return string[]
     */
    public function getTableNames() : array
    {
        return array_keys($this->tables);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasTable(string $name) : bool
    {
        return isset($this->tables[$name]);
    }

    /**
     * @param string $name
     *
     * @return Table
     * @throws InvalidArgumentException
     */
    public function getTable(string $name) : Table
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