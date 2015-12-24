<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Options\ArrayFieldOptions;
use Dms\Core\Form\Field\Processor\EnumProcessor;
use Dms\Core\Form\Field\Processor\Validator\OneOfValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Object\Enum;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\ScalarType as PhpScalarType;

/**
 * The enum field type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumType extends ScalarType
{
    const ATTR_ENUM_CLASS = 'enum-class';

    /**
     * EnumType constructor.
     *
     * @param string $enumClass
     * @param array  $valueLabelMap
     *
     * @throws InvalidArgumentException
     */
    public function __construct($enumClass, array $valueLabelMap)
    {
        if (!is_subclass_of($enumClass, Enum::class, true)) {
            throw InvalidArgumentException::format(
                    'Cannot construct enum field type: invalid enum class, expecting instance of %s, %s given',
                    Enum::class, $enumClass
            );
        };

        /** @var Enum|string $enumClass */
        $valueType = $enumClass::getEnumType();

        if (!($valueType instanceof PhpScalarType)) {
            throw InvalidArgumentException::format(
                    'Cannot construct enum field type: only enums containing scalar values are supported, %s given with values of type %s',
                    $enumClass, $valueType->asTypeString()
            );
        }

        $this->attributes[self::ATTR_ENUM_CLASS] = $enumClass;
        $this->attributes[self::ATTR_OPTIONS]    = ArrayFieldOptions::fromAssocArray(
                array_intersect_key(
                        $valueLabelMap,
                        array_flip($enumClass::getOptions())
                )
        );

        parent::__construct($valueType->asTypeString());
    }

    /**
     * @inheritDoc
     */
    protected function hasTypeSpecificOptionsValidator()
    {
        return true;
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        $enumValueType = Type::scalar($this->getType());

        if (!$this->get(self::ATTR_REQUIRED)) {
            $enumValueType = $enumValueType->nullable();
        }

        return array_merge(parent::buildProcessors(), [
                new OneOfValidator($enumValueType, $this->get(self::ATTR_OPTIONS)),
                new EnumProcessor($this->get(self::ATTR_ENUM_CLASS)),
        ]);
    }
}