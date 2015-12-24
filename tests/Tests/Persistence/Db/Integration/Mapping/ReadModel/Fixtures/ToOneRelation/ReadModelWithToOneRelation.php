<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ToOneRelation;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneRelation\SubEntity;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithToOneRelation extends ReadModel
{
    /**
     * @var SubEntity
     */
    public $subEntity;

    /**
     * ReadModelWithToOneRelation constructor.
     *
     * @param SubEntity $subEntity
     */
    public function __construct(SubEntity $subEntity)
    {
        parent::__construct();
        $this->subEntity = $subEntity;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->subEntity)->asObject(SubEntity::class);
    }
}