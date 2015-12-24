<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ReadModel;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithChildIds extends ReadModel
{
    /**
     * @var EntityIdCollection|int[]
     */
    public $childIds;

    /**
     * ReadModelWithLoadedToOneRelation constructor.
     *
     * @param int[] $childIds
     */
    public function __construct(array $childIds =[])
    {
        parent::__construct();
        $this->childIds = new EntityIdCollection($childIds);
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->childIds)->asCollectionOf(Type::int());
    }
}