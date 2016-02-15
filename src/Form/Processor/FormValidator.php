<?php declare(strict_types = 1);

namespace Dms\Core\Form\Processor;

use Dms\Core\Form\IFormProcessor;
use Dms\Core\Language\Message;

/**
 * The form validator base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class FormValidator implements IFormProcessor
{
    /**
     * {@inheritDoc}
     */
    public function process(array $input, array &$messages) : array
    {
        $this->validate($input, $messages);

        return $input;
    }

    /**
     * {@inheritDoc}
     */
    final public function unprocess(array $input) : array
    {
        return $input;
    }

    /**
     * @param array     $input
     * @param Message[] $messages
     *
     * @return void
     */
    abstract protected function validate(array $input, array &$messages);
}
