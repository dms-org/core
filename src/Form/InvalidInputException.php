<?php declare(strict_types = 1);

namespace Dms\Core\Form;

use Dms\Core\Exception\BaseException;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Language\Message;

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
    public function getField() : IField
    {
        return $this->field;
    }

    /**
     * @return Message[]
     */
    public function getMessages() : array
    {
        return $this->messages;
    }
}
