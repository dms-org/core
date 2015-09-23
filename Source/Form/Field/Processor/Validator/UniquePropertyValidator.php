<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Type\IType;

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
        $criteria = $this->objects->criteria()
                ->where($this->propertyName, '=', $input);

        if ($this->objects->countMatching($criteria) > 0) {
            $messages[] = new Message(self::MESSAGE, ['property_name' => $this->propertyName]);
        }
    }
}