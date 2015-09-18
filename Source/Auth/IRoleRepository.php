<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Model\EntityNotFoundException;
use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Exception;

interface IRoleRepository extends IRepository
{
    /**
     * Finds a role entity with the supplied name.
     *
     * @param string $name
     * @return IRole
     * @throws EntityNotFoundException
     */
    public function findByName($name);
}
