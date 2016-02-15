<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\IType;

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