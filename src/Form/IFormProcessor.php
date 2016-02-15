<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Language\Message;

/**
 * The form processor interface.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFormProcessor
{
    /**
     * Processes the form input and throws an exception
     * if the input does not meet the requirements.
     *
     * @param array     $input
     * @param Message[] $messages
     *
     * @return array
     */
    public function process(array $input, array &$messages) : array;

    /**
     * Performs the opposite operation on the input.
     * This should revert the processed value into the original input value.
     *
     * @param array $input
     *
     * @return array
     */
    public function unprocess(array $input) : array;

    /**
     * Returns an equivalent processor with the field names updated
     * from the supplied array containing the old field names as the key
     * and the new field names as the value.
     *
     * @param string[] $fieldNameMap
     *
     * @return static
     */
    public function withFieldNames(array $fieldNameMap);
}
