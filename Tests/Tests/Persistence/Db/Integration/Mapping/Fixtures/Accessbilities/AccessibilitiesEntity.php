<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\Accessbilities;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AccessibilitiesEntity extends Entity
{
    /**
     * @var int
     */
    private $private;

    /**
     * @var int
     */
    protected $protected;

    /**
     * @var int
     */
    public $public;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->private)->asInt();
        $class->property($this->protected)->asInt();
        $class->property($this->public)->asInt();
    }

    /**
     * @return int
     */
    public function getPrivate()
    {
        return $this->private;
    }

    /**
     * @param int $private
     */
    public function setPrivate($private)
    {
        $this->private = $private;
    }

    /**
     * @return int
     */
    public function getProtected()
    {
        return $this->protected;
    }

    /**
     * @param int $protected
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;
    }
}