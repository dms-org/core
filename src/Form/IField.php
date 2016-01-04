<?php

namespace Dms\Core\Form;

use Dms\Core\Model\Type\IType;

/**
 * The field interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IField
{
    /**
     * Gets the field name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the label name.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Gets the field input type data.
     *
     * @return IFieldType
     */
    public function getType();

    /**
     * Gets the processed type.
     *
     * @return IType
     */
    public function getProcessedType();

    /**
     * Gets the field processors.
     *
     * @return IFieldProcessor[]
     */
    public function getProcessors();

    /**
     * Gets the (processed) initial value of the field.
     *
     * @return mixed
     */
    public function getInitialValue();

    /**
     * Gets the unprocessed initial value of the field.
     *
     * @return mixed
     */
    public function getUnprocessedInitialValue();

    /**
     * Processes the field input.
     *
     * @param mixed $input
     *
     * @return mixed
     * @throws InvalidInputException
     */
    public function process($input);

    /**
     * Returns the processed field input back to the initial format.
     *
     * @param mixed $processedInput
     *
     * @return mixed
     */
    public function unprocess($processedInput);

    /**
     * Gets an equivalent form field with the supplied name and label.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return IField
     */
    public function withName($name, $label = null);

    /**
     * Gets an equivalent form field with the supplied (processed) initial value.
     *
     * @param mixed $value
     *
     * @return IField
     */
    public function withInitialValue($value);
}
