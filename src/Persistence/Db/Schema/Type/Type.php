<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Type\IType;

/**
 * The db type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Type
{
    /**
     * @var bool
     */
    private $nullable = false;

    /**
     * @return IType
     */
    final public function getPhpType() : IType
    {
        return $this->nullable
                ? $this->loadPhpType()->nullable()
                : $this->loadPhpType();
    }

    /**
     * @return IType
     */
    abstract protected function loadPhpType() : IType;

    /**
     * @return bool
     */
    public function isNullable() : bool
    {
        return $this->nullable;
    }

    /**
     * Sets the column type as nullable.
     *
     * @return static
     */
    public function nullable()
    {
        $clone = clone $this;

        $clone->nullable = true;

        return $clone;
    }

    /**
     * Gets the equivalent schema type from the supplied PHP value.
     *
     * @param $value
     *
     * @return Type
     * @throws InvalidArgumentException
     */
    public static function fromValue($value) : Type
    {
        switch (gettype($value)) {
            case 'string':
                return Text::long();

            case 'integer':
                return Integer::big();

            case 'double':
                return new Decimal(30, 15);

            case 'boolean':
                return new Boolean();

            case $value instanceof \DateTimeInterface:
                return new DateTime();

            default:
                throw InvalidArgumentException::format(
                        'Invalid call to %s: unknown value type \'%s\' given',
                        __METHOD__, gettype($value)
                );
        }
    }
}