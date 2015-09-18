<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The interface for the field processor.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFieldProcessor
{
    /**
     * Gets the ty[e of the processed data.
     *
     * @return IType
     */
    public function getProcessedType();

    /**
     * Processes the supplied input and adds any error messages
     * to the supplied messages array. No added messages implies
     * the input is valid and the processed value is returned.
     *
     * @param mixed     $input
     * @param Message[] $messages
     *
     * @return mixed
     */
    public function process($input, array &$messages);

    /**
     * Performs the opposite operation on the input.
     * This should revert the processed value into the original
     * input value if possible.
     *
     * @param mixed $input
     *
     * @return mixed
     */
    public function unprocess($input);
}
