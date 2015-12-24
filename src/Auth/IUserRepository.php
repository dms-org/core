<?php

namespace Dms\Core\Auth;

use Dms\Core\Exception;
use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Persistence\IRepository;

interface IUserRepository extends IRepository
{
    /**
     * Finds a user entity by the supplied email address.
     *
     * @param string $email
     * @return IUser
     * @throws EntityNotFoundException
     */
    public function findByEmailAddress($email);
}
