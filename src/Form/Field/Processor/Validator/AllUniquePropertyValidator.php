<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\IFieldProcessorDependentOnInitialValue;
use Dms\Core\Language\Message;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * The array unique value validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AllUniquePropertyValidator extends FieldValidator implements IFieldProcessorDependentOnInitialValue
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

    /**
     * @var array|null
     */
    private $initialValueHashes;

    /**
     * AllUniquePropertyValidator constructor.
     *
     * @param IType      $inputType
     * @param IObjectSet $objects
     * @param string     $propertyName
     * @param array|null $initialValue
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IType $inputType, IObjectSet $objects, string $propertyName, array $initialValue = null)
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

        if ($initialValue) {
            $this->initialValueHashes = [];

            foreach ($initialValue as $element) {
                $this->initialValueHashes[ValueHasher::hash($element)] = true;
            }
        }
    }

    /**
     * Returns an equivalent processor with the updated initial value
     *
     * @param mixed $initialValue
     *
     * @return static
     */
    public function withInitialValue($initialValue)
    {
        return new self($this->inputType, $this->objects, $this->propertyName, $initialValue);
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if ($this->initialValueHashes !== null) {
            // If the elements are contained within the initial value of the field
            // there we shouldn't check if the values are in the object set since
            // it will match the object
            foreach ($input as $key => $value) {
                if (isset($this->initialValueHashes[ValueHasher::hash($value)])) {
                    unset($input[$key]);
                }
            }
        }

        $criteria = $this->objects->criteria()
            ->whereIn($this->propertyName, $input);

        if ($this->objects->countMatching($criteria) > 0) {
            $messages[] = new Message(self::MESSAGE, ['property_name' => $this->propertyName]);
        }
    }
}