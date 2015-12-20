<?php

namespace Dms\Core\Auth;

use Dms\Core\Model\EntityNotFoundException;
use Dms\Core\Persistence\IRepository;
use Dms\Core\Exception;

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
