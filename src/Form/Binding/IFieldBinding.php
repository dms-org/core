<?php declare(strict_types = 1);

namespace Dms\Core\Form\Binding;

use Dms\Core\Form\Binding\Accessor\IFieldAccessor;

/**
 * The field binding interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldBinding
{
    /**
     * Gets the bound field name.
     *
     * @return string
     */
    public function getFieldName() : string;

    /**
     * Gets the expected object type.
     *
     * @return string
     */
    public function getObjectType() : string;

    /**
     * Gets the field accessor
     *
     * @return IFieldAccessor
     */
    public function getAccessor() : IFieldAccessor;
}