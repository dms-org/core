<?php declare(strict_types = 1);

namespace Dms\Core\Language;

use Dms\Core\Exception\BaseException;

/**
 * Exception to display a generic error message.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class ErrorMessageException extends BaseException
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * ErrorMessageException constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        parent::__construct($message->getFullId());
        $this->message = $message;
    }

    /**
     * @return Message
     */
    public function getLangMessage() : Message
    {
        return $this->message;
    }
}
