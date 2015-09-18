<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Model\Type\IType;

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
     * Gets an equivalent form field with the supplied name.
     *
     * @param string $name
     *
     * @return IField
     */
    public function withName($name);
}
