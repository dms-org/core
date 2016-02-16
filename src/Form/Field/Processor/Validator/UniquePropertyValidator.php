<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\IFieldProcessorDependentOnInitialValue;
use Dms\Core\Language\Message;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\IType;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * The unique value validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UniquePropertyValidator extends FieldValidator implements IFieldProcessorDependentOnInitialValue
{
    const MESSAGE = 'validation.unique';

    /**
     * @var IObjectSet
     */
    private $objects;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var mixed|null
     */
    private $initialValue;

    /**
     * UniquePropertyValidator constructor.
     *
     * @param IType      $inputType
     * @param IObjectSet $objects
     * @param string     $propertyName
     * @param mixed      $initialValue
     */
    public function __construct(IType $inputType, IObjectSet $objects, string $propertyName, $initialValue = null)
    {
        parent::__construct($inputType);
        $this->objects      = $objects;
        $this->propertyName = $propertyName;
        $this->initialValue = $initialValue;
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
        if ($this->initialValue !== null && ValueHasher::areEqual($input, $this->initialValue)) {
            // If the input is unchanged the value should still be unique and does
            // not need to be checked for whether it exists or not.
            return;
        }

        $criteria = $this->objects->criteria()
            ->where($this->propertyName, '=', $input);

        if ($this->objects->countMatching($criteria) > 0) {
            $messages[] = new Message(self::MESSAGE, ['property_name' => $this->propertyName]);
        }
    }
}