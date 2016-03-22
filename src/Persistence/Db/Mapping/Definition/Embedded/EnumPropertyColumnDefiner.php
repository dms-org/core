<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Embedded;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The enum property column definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumPropertyColumnDefiner
{
    /**
     * @var MapperDefinition
     */
    private $definition;

    /**
     * @var callable
     */
    private $enumCallback;

    /**
     * @var callable
     */
    private $columnCallback;

    /**
     * EnumPropertyColumnDefiner constructor.
     *
     * @param MapperDefinition $definition
     * @param callable         $enumCallback
     * @param callable         $columnCallback
     */
    public function __construct(MapperDefinition $definition, callable $enumCallback, callable $columnCallback)
    {
        $this->definition = $definition;

        $this->enumCallback   = $enumCallback;
        $this->columnCallback = $columnCallback;
    }

    /**
     * Defines the enum mapping to map to the supplied column name.
     *
     * @param string $columnName
     *
     * @return EnumPropertyDefiner
     */
    public function to(string $columnName) : EnumPropertyDefiner
    {
        return new EnumPropertyDefiner($this->definition, $this->enumCallback, $this->columnCallback, $columnName);
    }
}