<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The decimal points validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MaxDecimalPointsValidator extends FieldValidator
{
    const MESSAGE = 'validation.max-decimal-points';

    /**
     * @var int
     */
    private $maxDecimalPoints;

    /**
     * MaxDecimalPointsValidator constructor.
     *
     * @param IType $inputType
     * @param int   $maxDecimalPoints
     */
    public function __construct(IType $inputType, $maxDecimalPoints)
    {
        parent::__construct($inputType);
        $this->maxDecimalPoints = $maxDecimalPoints;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        if ((int)$input == $input) {
            return true;
        } else {
            $string        = rtrim(number_format($input, 8, '.', ''), '0');
            $decimalPoints = strlen(substr(strrchr($string, "."), 1));;
        }

        if ($this->maxDecimalPoints !== null & $decimalPoints > $this->maxDecimalPoints) {
            $messages[] = new Message(self::MESSAGE, ['max_decimal_points' => $this->maxDecimalPoints]);
        }
    }
}