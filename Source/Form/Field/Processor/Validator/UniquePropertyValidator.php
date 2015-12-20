<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Type\IType;

/**
 * The unique value validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UniquePropertyValidator extends FieldValidator
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

    public function __construct(IType $inputType, IObjectSet $objects, $propertyName)
    {
        parent::__construct($inputType);
        $this->objects      = $objects;
        $this->propertyName = $propertyName;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        // TODO: Account for fields which are same as the initial value
        $criteria = $this->objects->criteria()
                ->where($this->propertyName, '=', $input);

        if ($this->objects->countMatching($criteria) > 0) {
            $messages[] = new Message(self::MESSAGE, ['property_name' => $this->propertyName]);
        }
    }
}