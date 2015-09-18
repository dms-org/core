<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;


/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class HashedPassword extends ValueObject
{
    /**
     * @var string
     */
    public $hash;

    /**
     * @var string
     */
    public $algorithm;

    /**
     * HashedPassword constructor.
     *
     * @param string $hash
     * @param string $algorithm
     */
    public function __construct($hash, $algorithm)
    {
        parent::__construct();
        $this->hash      = $hash;
        $this->algorithm = $algorithm;
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
    }
}