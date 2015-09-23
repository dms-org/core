<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Type\ArrayType;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The array unique value validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AllUniquePropertyValidator extends FieldValidator
{
    const MESSAGE = 'validation.all-unique';

    /**
     * @var IObjectSet
     */
    private $objects;

    /**
     * @var string
     */
    private $propertyName;

    public function __construct(IType $inputType, IObjectSet $objects, $propertyName)
    {
        if (!($inputType->nonNullable() instanceof ArrayType)) {
            throw InvalidArgumentException::format(
                    'Invalid input type passed to %s: expecting array, %s given',
                    __METHOD__, $inputType->asTypeString()
            );
        }

        parent::__construct($inputType);
        $this->objects      = $objects;
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        $criteria = $this->objects->criteria()
                ->whereIn($this->propertyName, $input);

        if ($this->objects->countMatching($criteria) > 0) {
            $messages[] = new Message(self::MESSAGE, ['property_name' => $this->propertyName]);
        }
    }
}