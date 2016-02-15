<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Model\ITypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IAccessor
{
    /**
     * @param ITypedObject $object
     * @param array        $properties
     *
     * @return mixed
     */
    public function get(ITypedObject $object, array $properties);

    /**
     * @param ITypedObject $object
     * @param array        $properties
     * @param mixed        $value
     *
     * @return void
     */
    public function set(ITypedObject $object, array &$properties, $value);
}