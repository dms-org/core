<?php

namespace Iddigital\Cms\Core\Language;

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
}