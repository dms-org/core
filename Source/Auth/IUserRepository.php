<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\EntityNotFoundException;
use Iddigital\Cms\Core\Persistence\IRepository;

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
