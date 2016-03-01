<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Type\StringType;

/**
 * The string field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringFieldBuilder extends FieldBuilderBase
{
    /**
     * Converts empty strings ('') to nulls.
     *
     * @return static
     */
    public function withEmptyStringAsNull()
    {
        return $this->attr(StringType::ATTR_EMPTY_STRING_AS_NULL, true);
    }

    /**
     * Trims the input string of the supplied characters.
     *
     * @param string $characters
     *
     * @return static
     */
    public function trim(string $characters = " \t\n\r\0\x0B")
    {
        return $this->attr(StringType::ATTR_TRIM_CHARACTERS, $characters);
    }

    /**
     * Sets the field type as a multi-line string.
     *
     * @return static
     */
    public function multiline()
    {
        return $this->attr(StringType::ATTR_MULTILINE, true);
    }

    /**
     * Validates the input as an email address.
     *
     * @return static
     */
    public function email()
    {
        return $this->attr(StringType::ATTR_STRING_TYPE, StringType::TYPE_EMAIL);
    }

    /**
     * Validates the input as an url.
     *
     * @return static
     */
    public function url()
    {
        return $this->attr(StringType::ATTR_STRING_TYPE, StringType::TYPE_URL);
    }

    /**
     * Sets the field type as a password.
     *
     * @return static
     */
    public function password()
    {
        return $this->attr(StringType::ATTR_STRING_TYPE, StringType::TYPE_PASSWORD);
    }

    /**
     * Sets the field type as html.
     *
     * @return static
     */
    public function html()
    {
        return $this->attr(StringType::ATTR_STRING_TYPE, StringType::TYPE_HTML);
    }

    /**
     * Sets the field type as an ip address.
     *
     * @return static
     */
    public function ipAddress()
    {
        return $this->attr(StringType::ATTR_STRING_TYPE, StringType::TYPE_IP_ADDRESS);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function exactLength(int $length)
    {
        return $this->attr(StringType::ATTR_EXACT_LENGTH, $length);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function minLength(int $length)
    {
        return $this->attr(StringType::ATTR_MIN_LENGTH, $length);
    }

    /**
     * Validates the input has an exact string length.
     *
     * @param int $length
     *
     * @return static
     */
    public function maxLength(int $length)
    {
        return $this->attr(StringType::ATTR_MAX_LENGTH, $length);
    }
}