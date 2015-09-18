<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\LoadToOneIdRelation;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ToOneIdRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithSubEntityId extends ReadModel
{
    /**
     * @var int
     */
    public $subEntityId;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param int $subEntityId
     */
    public function __construct($subEntityId)
    {
        parent::__construct();
        $this->subEntityId = $subEntityId;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->subEntityId)->asInt();
    }
}