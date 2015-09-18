<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\IEntity;

/**
 * The create entity form object interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ICreateEntityFormObject extends IDataTransferObject
{
    /**
     * Constructs a new entity with the populated state from the form.
     *
     * @return IEntity
     */
    public function populateNewEntity();
}