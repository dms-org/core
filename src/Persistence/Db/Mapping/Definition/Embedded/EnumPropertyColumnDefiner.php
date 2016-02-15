<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Embedded;

/**
 * The enum property column definer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumPropertyColumnDefiner
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
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
        return new EnumPropertyDefiner($this->callback, $columnName);
    }
}