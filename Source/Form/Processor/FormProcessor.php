<?php

namespace Iddigital\Cms\Core\Form\Processor;

use Iddigital\Cms\Core\Form\IFormProcessor;
use Iddigital\Cms\Core\Language\Message;

/**
 * The form processor base class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
abstract class FormProcessor implements IFormProcessor
{
    /**
     * {@inheritDoc}
     */
    public function process(array $input, array &$messages)
    {
        return $this->doProcess($input, $messages);
    }

    /**
     * {@inheritDoc}
     */
    public function unprocess(array $input)
    {
        return $this->doUnprocess($input);
    }

    /**
     * @param array     $input
     * @param Message[] $messages
     *
     * @return array
     */
    abstract protected function doProcess(array $input, array $messages);

    /**
     * @param array $input
     *
     * @return array
     */
    abstract protected function doUnprocess(array $input);
}
