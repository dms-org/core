<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Embedded;

/**
 * The enum property definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumPropertyDefiner
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var string
     */
    private $columnName;

    public function __construct(callable $callback, $columnName)
    {
        $this->callback   = $callback;
        $this->columnName = $columnName;
    }

    /**
     * Defines the enum mapping to use the values defined
     * in the enum class constants.
     *
     * @return void
     */
    public function usingValuesFromConstants()
    {
        call_user_func($this->callback, $this->columnName, null);
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
        call_user_func($this->callback, $this->columnName, $enumValueMap);
    }
}