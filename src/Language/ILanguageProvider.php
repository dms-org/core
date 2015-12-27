<?php

namespace Dms\Core\Language;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The language provider interface
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ILanguageProvider
{
    /**
     * Gets the fully formed message string from the supplied message id
     * and parameters
     *
     * @param Message $message
     *
     * @return string
     * @throws MessageNotFoundException
     */
    public function format(Message $message);

    /**
     * Gets the fully formed message strings from the supplied message ids
     * and parameters
     *
     * @param Message[] $messages
     *
     * @return string[]
     * @throws InvalidArgumentException
     * @throws MessageNotFoundException
     */
    public function formatAll(array $messages);
}