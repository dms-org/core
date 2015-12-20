<?php

namespace Dms\Core\Form\Object;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\ITypedObject;

/**
 * The entity form object base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EntityFormObject extends TypedObjectFormObject
{
    public function __construct(IEntity $entity = null)
    {
        parent::__construct($entity);
    }

    /**
     * Gets the entity or null if there is no entity.
     *
     * @return IEntity|null
     */
    final public function getEntity()
    {
        /** @var IEntity|null $entity */
        $entity = $this->getObject();

        return $entity;
    }

    /**
     * @inheritDoc
     */
    final protected function objectType()
    {
        return $this->entityType();
    }

    /**
     * {@inheritDoc}
     */
    final protected function populateFormWithObject(ITypedObject $object)
    {
        /** @var IEntity $object */
        $this->populateFormWithEntity($object);
    }

    /**
     * Populates the object's state with the form's values.
     *
     * @param IEntity $entity
     *
     * @return void
     */
    final public function populateEntity(IEntity $entity)
    {
        $this->verifyObject($entity);
        $this->populateEntityWithForm($entity);
    }

    /**
     * Gets the type of entity of this form.
     *
     * @return string
     */
    abstract protected function entityType();

    /**
     * Populates the form with the entity's values.
     *
     * @param IEntity $entity
     *
     * @return string
     */
    abstract protected function populateFormWithEntity(IEntity $entity);

    /**
     * Populates the form with the entity's values.
     *
     * @param IEntity $entity
     *
     * @return string
     */
    abstract protected function populateEntityWithForm(IEntity $entity);
}