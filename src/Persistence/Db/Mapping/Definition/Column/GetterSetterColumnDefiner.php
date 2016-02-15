<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Column;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The getter setter column mapping definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GetterSetterColumnDefiner
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
     * GetterSetterColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $callback
     */
    public function __construct(MapperDefinition $definition, callable $callback)
    {
        $this->definition   = $definition;
        $this->callback     = $callback;
    }

    /**
     * Maps the accessor to the supplied column name.
     *
     * @param string $columnName
     *
     * @return ColumnTypeDefiner
     */
    public function to(string $columnName) : ColumnTypeDefiner
    {
        return new ColumnTypeDefiner(
                $this->definition,
                $this->callback,
                $columnName
        );
    }
}