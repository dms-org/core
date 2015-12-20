<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToOneIdRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

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