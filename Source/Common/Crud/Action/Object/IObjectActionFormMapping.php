<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;

/**
 * The object action form mapping interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IObjectActionFormMapping extends IStagedFormDtoMapping
{
    /**
     * Gets the mapped data dto type or NULL if no type is mapped.
     *
     * @return string|null
     */
    public function getDataDtoType();
}