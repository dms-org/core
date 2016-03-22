<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Embedded;
use Dms\Core\Persistence\Db\Mapping\Definition\Column\ColumnTypeDefiner;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The enum property definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumPropertyDefiner extends ColumnTypeDefiner
{
    /**
     * @var callable
     */
    private $enumCallback;

    public function __construct(MapperDefinition $definition, callable $enumCallback, callable $columnCallback, $columnName)
    {
        parent::__construct($definition, $columnCallback, $columnName, false);
        $this->enumCallback = $enumCallback;
    }

    /**
     * Defines the enum mapping to use the values defined
     * in the enum class constants.
     *
     * @return void
     */
    public function usingValuesFromConstants()
    {
        call_user_func($this->enumCallback, $this->name, null);
    }

    /**
     * Defines the enum mapping to use the supplied value map
     * which defined the enum constants in the array keys mapping
     * to the equivalent value in the array values.
     *
     * @param array $enumValueMap
     *
     * @return void
     */
    public function usingValueMap(array $enumValueMap)
    {
        call_user_func($this->enumCallback, $this->name, $enumValueMap);
    }
}