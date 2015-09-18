<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The decimal points validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DecimalPointsValidator extends FieldValidator
{
    const MESSAGE_MIN = 'validation.decimal-points.min';
    const MESSAGE_MAX = 'validation.decimal-points.max';

    /**
     * @var int|null
     */
    private $minDecimalPoints;
    /**
     * @var int|null
     */
    private $maxDecimalPoints;

    public function __construct(IType $inputType, $minDecimalPoints, $maxDecimalPoints)
    {
        parent::__construct($inputType);
        $this->minDecimalPoints = $minDecimalPoints;
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

        if ($this->minDecimalPoints !== null & $decimalPoints < $this->minDecimalPoints) {
            $messages[] = new Message(self::MESSAGE_MIN, ['min_decimal_points' => $this->minDecimalPoints]);
        }

        if ($this->maxDecimalPoints !== null & $decimalPoints > $this->maxDecimalPoints) {
            $messages[] = new Message(self::MESSAGE_MAX, ['max_decimal_points' => $this->maxDecimalPoints]);
        }
    }
}