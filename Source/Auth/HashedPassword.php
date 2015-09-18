<?php

namespace Iddigital\Cms\Core\Auth;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;

/**
 * The hashed password value object.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class HashedPassword extends ValueObject implements IHashedPassword
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var int
     */
    private $costFactor;

    /**
     * HashedPassword constructor.
     *
     * @param string $hash
     * @param string $algorithm
     * @param int    $costFactor
     */
    public function __construct($hash, $algorithm, $costFactor)
    {
        parent::__construct();
        $this->hash       = $hash;
        $this->algorithm  = $algorithm;
        $this->costFactor = $costFactor;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->hash)->asString();
        $class->property($this->algorithm)->asString();
        $class->property($this->costFactor)->asInt();
    }

    /**
     * {@inheritDoc}
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * {@inheritDoc}
     */
    public function getCostFactor()
    {
        return $this->costFactor;
    }
}