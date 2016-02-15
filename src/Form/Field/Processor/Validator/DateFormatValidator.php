<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

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