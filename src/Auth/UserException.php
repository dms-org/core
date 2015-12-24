<?php

namespace Dms\Core\Auth;

use Dms\Core\Exception\BaseException;

/**
 * Exception involving an user.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class UserException extends BaseException
{
    /**
     * @var IUser
     */
    private $user;

    public function __construct(IUser $user, $message, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    /**
     * @return IUser
     */
    public function getUser()
    {
        return $this->user;
    }
}
