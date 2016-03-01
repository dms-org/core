<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Schema\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\IType;

/**
 * The db enum type
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Enum extends Type
{
    /**
     * @var mixed[]
     */
    private $options;

    /**
     * Enum constructor.
     *
     * @param mixed[] $options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $options)
    {
        if (empty($options)) {
            throw InvalidArgumentException::format('Invalid argument supplied to %s: options cannot be empty', __METHOD__);
        }

        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    protected function loadPhpType() : IType
    {
        /** @var ArrayType $type */
        $type = PhpType::from($this->options);

        return $type->getElementType();
    }

    /**
     * @return string
     */
    public function getPhpVariableType() : string
    {
        $type = $this->loadPhpType()->nonNullable();

        return [
                       IType::STRING => 'string',
                       IType::INT    => 'integer',
                       IType::BOOL   => 'boolean',
                       IType::FLOAT  => 'double',
               ][$type->asTypeString()];
    }

    /**
     * @return string[]
     */
    public function getOptions() : array
    {
        return $this->options;
    }
}