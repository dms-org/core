<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Language\Message;

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
    public function process(array $input, array &$messages);

    /**
     * Performs the opposite operation on the input.
     * This should revert the processed value into the original input value.
     *
     * @param array $input
     *
     * @return array
     */
    public function unprocess(array $input);
}
