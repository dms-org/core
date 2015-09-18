<?php

namespace Iddigital\Cms\Core\Common\Crud\Result;

use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\DataTransferObject;

/**
 * The entity result base dto
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityResult extends DataTransferObject
{
    /**
     * @var IEntity
     */
    public $entity;

    /**
     * EntityCreatedResult constructor.
     *
     * @param IEntity $entity
     */
    public function __construct(IEntity $entity)
    {
        parent::__construct();
        $this->entity = $entity;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->entity)->asType(IEntity::class);
    }
}