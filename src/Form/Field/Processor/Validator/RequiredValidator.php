<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The required field validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RequiredValidator extends FieldValidator
{
    const MESSAGE = 'validation.required';

    /**
     * @var IType
     */
    protected $processedType;

    /**
     * @inheritDoc
     */
    public function __construct(IType $inputType)
    {
        parent::__construct($inputType);
        $this->processedType = $inputType->nonNullable();
    }

    /**
     * @inheritDoc
     */
    public function getProcessedType() : \Dms\Core\Model\Type\IType
    {
        return $this->processedType;
    }

    public function process($input, array &$messages)
    {
        if ($input === null || $input === '' || $input === false) {
            $messages[] = new Message(self::MESSAGE);
            return $input;
        } else {
            return parent::process($input, $messages);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        return $input;
    }
}