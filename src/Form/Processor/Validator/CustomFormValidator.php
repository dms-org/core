<?php declare(strict_types = 1);

namespace Dms\Core\Form\Processor\Validator;

use Dms\Core\Form\Processor\ArrayKeyHelper;
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
        if (!call_user_func_array($this->validationCallback, [$input, &$messages])) {
            if ($this->messageId) {
                $messages[] = new Message($this->messageId, $this->messageParameters);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function withFieldNames(array $fieldNameMap)
    {
        $inverseFieldMap = array_flip($fieldNameMap);

        return new self(
                function (array $input, array &$messages) use ($fieldNameMap, $inverseFieldMap) {
                    return call_user_func_array(
                            $this->validationCallback,
                            [ArrayKeyHelper::mapArrayKeys($input, $inverseFieldMap), &$messages]
                    );
                },
                $this->messageId,
                $this->messageParameters
        );
    }
}
