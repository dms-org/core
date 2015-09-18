<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ManyToManyRelation\Bidirectional;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\EntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnotherEntityMapper extends EntityMapper
{
    /**
     * @var OneEntityMapper
     */
    private $oneMapper;

    /**
     * @inheritDoc
     */
    public function __construct(OneEntityMapper $one)
    {
        $this->oneMapper = $one;
        parent::__construct('anothers');
    }

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(AnotherEntity::class);

        $map->idToPrimaryKey('id');

        $map->relation('ones')
            ->using($this->oneMapper)
            ->toMany()
            ->withBidirectionalRelation('others')
            ->throughJoinTable('one_anothers')
            ->withParentIdAs('another_id')
            ->withRelatedIdAs('one_id');
    }
}