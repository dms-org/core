<?php

namespace Dms\Core\Form\Processor\Validator;

use Dms\Core\Form\Processor\FormValidator;
use Dms\Core\Language\Message;

/**
 * The custom form validator.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class CustomFormValidator extends FormValidator
{
    /**
     * @var callable
     */
    protected $validationCallback;

    /**
     * @var string|null
     */
    protected $messageId;

    /**
     * @var string[]
     */
    protected $messageParameters;

    public function __construct(callable $validationCallback, $messageId = null, array $messageParameters = [])
    {
        $this->validationCallback = $validationCallback;
        $this->messageId          = $messageId;
        $this->messageParameters  = $messageParameters;
    }

    /**
     * @param array     $input
     * @param Message[] $messages
     *
     * @return void
     */
    protected function validate(array $input, array &$messages)
    {
        if (!call_user_func($this->validationCallback, $input, $messages)) {
            if($this->messageId) {
                $messages[] = new Message($this->messageId, $this->messageParameters);
            }
        }
    }
}
