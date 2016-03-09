<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Exception\BaseException;

/**
 * Exception involving an user.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class AdminException extends BaseException
{
    /**
     * @var IAdmin
     */
    private $user;

    public function __construct(IAdmin $admin, $message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->user = $admin;
    }

    /**
     * @return IAdmin
     */
    public function getUser() : IAdmin
    {
        return $this->user;
    }
}
