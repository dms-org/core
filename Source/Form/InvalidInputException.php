<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Language\Message;

/**
 * Exception for invalid input.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class InvalidInputException extends BaseException
{
    /**
     * @var IField
     */
    protected $field;

    /**
     * @var Message[]
     */
    protected $messages;

    /**
     * @param IField    $field
     * @param Message[] $messages
     */
    public function __construct(IField $field, array $messages)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'messages', $messages, Message::class);

        $messageIds = [];

        foreach ($messages as $message) {
            $messageIds[] = $message->getId();
        }

        parent::__construct("The field '{$field->getName()}' has invalid input: validation messages " . implode(', ', $messageIds));

        $this->field    = $field;
        $this->messages = $messages;
    }

    /**
     * @return IField
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
