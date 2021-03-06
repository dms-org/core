<?php declare(strict_types = 1);

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
    public function format(Message $message) : string;

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
    public function formatAll(array $messages) : array;

    /**
     * Adds a resource directory which will load translations in the supplied namespace.
     *
     * @param string $namespace
     * @param string $directory
     *
     * @return void
     */
    public function addResourceDirectory(string $namespace, string $directory);
}