<?php declare(strict_types = 1);

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
    public function getName() : string;

    /**
     * Gets the label name.
     *
     * @return string
     */
    public function getLabel() : string;

    /**
     * Gets the field input type data.
     *
     * @return IFieldType
     */
    public function getType() : IFieldType;

    /**
     * Gets the processed type.
     *
     * @return IType
     */
    public function getProcessedType() : IType;

    /**
     * Gets the field processors.
     *
     * @return IFieldProcessor[]
     */
    public function getProcessors() : array;

    /**
     * Gets the custom field processors.
     *
     * @return IFieldProcessor[]
     */
    public function getCustomProcessors() : array;

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
     * Returns an equivalent form field with the supplied name and label.
     *
     * @param string      $name
     * @param string|null $label
     *
     * @return IField
     */
    public function withName(string $name, string $label = null) : IField;

    /**
     * Returns an equivalent form field with the supplied (processed) initial value.
     *
     * @param mixed $value
     *
     * @return IField
     */
    public function withInitialValue($value) : IField;

    /**
     * Returns an equivalent form field with the supplied custom processors.
     *
     * NOTE: The initial value of the field will be removed.
     *
     * @param IFieldProcessor[] $customProcessors
     *
     * @return IField
     */
    public function withCustomProcessors(array $customProcessors) : IField;

    /**
     * Returns an equivalent form field with updated field type attribute.
     *
     * @param array $attributes
     *
     * @return IField
     */
    public function withTypeAttributes(array $attributes) : IField;
}
