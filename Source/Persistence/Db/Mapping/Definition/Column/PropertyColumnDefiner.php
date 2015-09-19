<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Column;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The property column mapping definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyColumnDefiner
{
    /**
     * @var MapperDefinition
     */
    private $definition;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var callable|null
     */
    private $phpToDbConverter;

    /**
     * @var callable|null
     */
    private $dbToPhpConverter;

    /**
     * PropertyColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $definition, callable $callback)
    {
        $this->definition = $definition;
        $this->callback   = $callback;
    }

    /**
     * Defines the callbacks to use to map the property values
     * to and from the database.
     *
     * @param callable $phpToDbConverter
     * @param callable $dbToPhpConverter
     *
     * @return static
     */
    public function mappedVia(callable $phpToDbConverter, callable $dbToPhpConverter)
    {
        $this->phpToDbConverter = $phpToDbConverter;
        $this->dbToPhpConverter = $dbToPhpConverter;

        return $this;
    }

    /**
     * Maps the property to the supplied column name.
     *
     * @param string $columnName
     *
     * @return ColumnTypeDefiner
     */
    public function to($columnName)
    {
        return new ColumnTypeDefiner(
                $this->definition,
                $this->callback,
                $this->phpToDbConverter,
                $this->dbToPhpConverter,
                $columnName
        );
    }
}