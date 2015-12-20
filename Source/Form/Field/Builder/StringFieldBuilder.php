<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\TrimProcessor;
use Dms\Core\Form\Field\Processor\Validator\EmailValidator;
use Dms\Core\Form\Field\Processor\Validator\ExactLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MaxLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\MinLengthValidator;
use Dms\Core\Form\Field\Processor\Validator\UrlValidator;
use Dms\Core\Form\Field\Type\StringType;

/**
 * The string field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringFieldBuilder extends FieldBuilderBase
{
    /**
     * Trims the input string of the supplied characters.
     *
     * @param string|null $characters
     *
     * @return static
     */
    public function trim($characters = null)
    {
        return $this->process(new TrimProcessor($characters));
    }

    /**
     * Validates the input as an email address.
     *
     * @return static
     */
    public function email()
    {
        return $this->validate(new EmailValidator($this->getCurrentProcessedType()))
            ->attr(StringType::ATTR_TYPE, StringType::TYPE_EMAIL);
    }

    /**
     * Validates the input as an url.
     *
     * @return static
     */
    public function url()
    {
        return $this->validate(new UrlValidator($this->getCurrentProcessedType()))
            ->attr(StringType::ATTR_TYPE, StringType::TYPE_URL);
    }

    /**
     * Sets the field type as a password.
     *
     * @return static
     */
    public function password()
    {
        return $this->attr(StringType::ATTR_TYPE, StringType::TYPE_PASSWORD);
    }

    /**
     * Sets the field type as html.
     *
     * @return static
     */
    public function html()
    {
        return $this->attr(StringType::ATTR_TYPE, StringType::TYPE_HTML);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function exactLength($length)
    {
        return $this->validate(new ExactLengthValidator($this->getCurrentProcessedType(), $length))
            ->attr(StringType::ATTR_MIN_LENGTH, $length)
            ->attr(StringType::ATTR_MAX_LENGTH, $length);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function minLength($length)
    {
        return $this->validate(new MinLengthValidator($this->getCurrentProcessedType(), $length))
            ->attr(StringType::ATTR_MIN_LENGTH, $length);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function maxLength($length)
    {
        return $this->validate(new MaxLengthValidator($this->getCurrentProcessedType(), $length))
            ->attr(StringType::ATTR_MAX_LENGTH, $length);
    }
}