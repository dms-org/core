<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;

/**
 * The array read model class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayReadModel extends ReadModel
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->data)->ignore();
    }
}