<?php declare(strict_types = 1);

namespace Dms\Core\Auth;

use Dms\Core\Model\IValueObject;

interface IHashedPassword extends IValueObject
{
    /**
     * Gets the hashed password string.
     *
     * @return string
     */
    public function getHash() : string;

    /**
     * Gets the hashing algorithm.
     *
     * @return string
     */
    public function getAlgorithm() : string;

    /**
     * Gets the hashing cost factor.
     *
     * @return int
     */
    public function getCostFactor() : int;
}
