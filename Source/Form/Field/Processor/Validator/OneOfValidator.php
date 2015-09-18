<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The one of validator that asserts that the input
 * is one of the supplied options.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class OneOfValidator extends FieldValidator
{
    const MESSAGE = 'validation.one-of';

    /**
     * @var array
     */
    protected $options;

    public function __construct(IType $inputType, array $options)
    {
        parent::__construct($inputType);
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if (!in_array($input, $this->options, true)) {
            $messages[] = new Message(self::MESSAGE, ['options' => implode(', ', $this->options)]);
        }
    }
}