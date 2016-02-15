<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Type\ObjectType;

/**
 * The enum field processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumProcessor extends FieldProcessor
{
    /**
     * @var string
     */
    private $enumClass;

    /**
     * @param string $enumClass
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $enumClass)
    {
        parent::__construct(new ObjectType($enumClass));

        if (!is_subclass_of($enumClass, Enum::class, true)) {
            throw InvalidArgumentException::format(
                    'Invalid enum class: expecting instance of %s, %s given',
                    Enum::class, $enumClass
            );
        };

        $this->enumClass = $enumClass;
    }

    protected function doProcess($input, array &$messages)
    {
        $enumClass = $this->enumClass;

        return new $enumClass($input);
    }

    protected function doUnprocess($input)
    {
        /** @var Enum $input */
        return $input->getValue();
    }
}