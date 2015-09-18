<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The field type validator base class.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeValidator extends FieldValidator
{
    const MESSAGE = 'validation.type';

    /**
     * @var IType
     */
    private $expectedType;

    public function __construct(IType $expectedType)
    {
        $inputType = Type::mixed();
        parent::__construct($inputType);

        $this->expectedType = $expectedType;
    }

    /**
     * @inheritDoc
     */
    public function getProcessedType()
    {
        return $this->expectedType;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!$this->expectedType->isOfType($input)) {
            $messages[] = new Message(self::MESSAGE, ['type' => $this->expectedType->asTypeString()]);
        }
    }
}