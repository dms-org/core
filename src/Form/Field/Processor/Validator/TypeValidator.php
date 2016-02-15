<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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
    public function getProcessedType() : \Dms\Core\Model\Type\IType
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