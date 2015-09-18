<?php

namespace Iddigital\Cms\Core\Form;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Language\Message;

/**
 * Exception for an unmet form constraint.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UnmetConstraintException extends BaseException
{
    /**
     * @var IFormProcessor
     */
    protected $processor;

    /**
     * @var Message[]
     */
    protected $messages;

    /**
     * @param IFormProcessor $processor
     * @param Message[]      $messages
     */
    public function __construct(IFormProcessor $processor, array $messages)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'messages', $messages, Message::class);

        $messageIds = [];

        foreach ($messages as $message) {
            $messageIds[] = $message->getId();
        }

        parent::__construct("The form has invalid input: validation messages " . implode(', ', $messageIds));

        $this->processor = $processor;
        $this->messages  = $messages;
    }

    /**
     * @return IFormProcessor
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
