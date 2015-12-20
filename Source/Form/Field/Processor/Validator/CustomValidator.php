<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The custom validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomValidator extends FieldValidator
{
    /**
     * @var callable
     */
    private $validateCallback;

    /**
     * @var string|null
     */
    private $messageId;

    /**
     * @var string[]
     */
    private $parameters;

    public function __construct(IType $inputType, callable $validateCallback, $messageId = null, array $parameters = [])
    {
        parent::__construct($inputType);
        $this->validateCallback = $validateCallback;
        $this->messageId        = $messageId;
        $this->parameters       = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        $result = call_user_func_array($this->validateCallback, [$input, &$messages]);

        if ($result !== true && $this->messageId !== null) {
            $messages[] = new Message($this->messageId, $this->parameters);
        }
    }
}