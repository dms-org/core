<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Column;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

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
     * @var string|null
     */
    private $propertyName;

    /**
     * @var bool
     */
    private $ignoreNullabilityTypeMismatch = false;

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
     * @param string|null      $propertyName
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $definition, string $propertyName = null, callable $callback)
    {
        $this->definition   = $definition;
        $this->propertyName = $propertyName;
        $this->callback     = $callback;
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
     * Defines that a type mismatch between the column nullability
     * and the property nullability should be ignored.
     *
     * @return static
     */
    public function ignoreNullabilityTypeMismatch()
    {
        $this->ignoreNullabilityTypeMismatch = true;

        return $this;
    }

    /**
     * Maps the property to the supplied column name.
     *
     * @param string $columnName
     *
     * @return ColumnTypeDefinerWithVersioning
     */
    public function to(string $columnName) : ColumnTypeDefinerWithVersioning
    {
        return new ColumnTypeDefinerWithVersioning(
                $this->definition,
                $this->callback,
                $this->phpToDbConverter,
                $this->dbToPhpConverter,
                $this->propertyName,
                $columnName,
                $this->ignoreNullabilityTypeMismatch
        );
    }
}