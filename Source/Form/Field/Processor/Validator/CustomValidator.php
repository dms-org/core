<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

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