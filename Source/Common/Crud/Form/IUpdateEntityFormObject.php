<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\IEntity;

/**
 * The update entity form object interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IUpdateEntityFormObject extends IDataTransferObject
{
    /**
     * Gets the entity of the form.
     *
     * @return IEntity
     */
    public function getEntity();

    /**
     * Populates the entity's state with the values from the form.
     *
     * @param IEntity $entity
     *
     * @return void
     */
    public function populateEntity(IEntity $entity);
}