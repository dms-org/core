<?php

namespace Dms\Core\Form\Processor\Validator;

use Dms\Core\Form\IField;
use Dms\Core\Form\Processor\FormValidator;
use Dms\Core\Language\Message;

/**
 * The matching fields form validator.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class MatchingFieldsValidator extends FormValidator
{
    const MESSAGE = 'validation.matching-fields';

    /**
     * @var IField
     */
    protected $field1;

    /**
     * @var IField
     */
    protected $field2;

    /**
     * MatchingFieldsValidator constructor.
     *
     * @param IField $field1
     * @param IField $field2
     */
    public function __construct(IField $field1, IField $field2)
    {
        $this->field1 = $field1;
        $this->field2 = $field2;
    }

    /**
     * @param array     $input
     * @param Message[] $messages
     *
     * @return void
     */
    protected function validate(array $input, array &$messages)
    {
        $field1Name = $this->field1->getName();
        $field2Name = $this->field2->getName();

        if (!isset($input[$field1Name])
                || !isset($input[$field2Name])
                || !$this->valuesMatch($input[$field1Name], $input[$field2Name])
        ) {
            $messages[] = new Message(
                    self::MESSAGE,
                    ['field1' => $this->field1->getLabel(), 'field2' => $this->field2->getLabel()]
            );
        }
    }

    protected function valuesMatch($value1, $value2)
    {
        if (gettype($value1) !== gettype($value2)) {
            return false;
        }

        if (is_object($value1) || is_array($value1)) {
            return $value1 == $value2;
        }

        return $value1 === $value2;
    }
}
