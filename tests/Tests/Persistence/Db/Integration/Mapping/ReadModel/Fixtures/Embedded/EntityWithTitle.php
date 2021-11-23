<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Persistence\Db\Mapping\ReadModel\GenericReadModelMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityWithTitle extends Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @inheritDoc
     */
    public function __construct($id = null, $title = null)
    {
        parent::__construct($id);
        $this->title = $title;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->title)->asString();
    }
}