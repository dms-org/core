<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleEntity extends Entity
{
    const DATA = 'data';

    /**
     * @var string
     */
    public $data;

    /**
     * @inheritDoc
     */
    public function __construct($id, $data)
    {
        parent::__construct($id);
        $this->data = $data;
    }


    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->data)->asString();
    }
}