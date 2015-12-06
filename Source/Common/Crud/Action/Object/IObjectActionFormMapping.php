<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;

/**
 * The object action form mapping interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectActionFormMapping extends IStagedFormDtoMapping
{
    /**
     * Gets the first stage of the action. This contains the object field.
     *
     * The form will be equivalent to the form defined in the object from class.
     * @see ObjectForm
     *
     * @return IForm
     */
    public function getObjectForm();

    /**
     * Gets the mapped data dto type or NULL if no type is mapped.
     *
     * @return string|null
     */
    public function getDataDtoType();
}