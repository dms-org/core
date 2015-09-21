<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\LoadToManyIdRelation;

use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ReadModel;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Relations\Fixtures\ToOneIdRelation\SubEntity;

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