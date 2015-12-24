<?php

namespace Dms\Core\Auth;

use Dms\Core\Exception;
use Dms\Core\IPackage;

interface IPasswordHasherFactory extends IPackage
{
    /**
     * Builds a password hasher with the supplied settings
     *
     * @param string $algorithm
     * @param int    $costFactor
     *
     * @return IPasswordHasher
     * @throws Exception\InvalidArgumentException
     */
    public function build($algorithm, $costFactor);

    /**
     * Builds a password hasher matching the supplied hashed password
     *
     * @param IHashedPassword $hashedPassword
     *
     * @return IPasswordHasher
     * @throws Exception\InvalidArgumentException
     */
    public function buildFor(IHashedPassword $hashedPassword);
}
