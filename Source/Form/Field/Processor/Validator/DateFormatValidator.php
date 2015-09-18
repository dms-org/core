<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The date form validator.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateFormatValidator extends FieldValidator
{
    const MESSAGE = 'validation.date-format';

    /**
     * @var string
     */
    private $format;

    public function __construct(IType $inputType, $format)
    {
        parent::__construct($inputType);
        $this->format = $format;
    }



    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if(!@date_create_from_format($this->format, $input)) {
            $messages[] = new Message(self::MESSAGE, ['format' => $this->format]);
        }
    }
}