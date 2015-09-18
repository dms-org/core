<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject\LevelOne;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject\LevelThree;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject\LevelTwo;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject\ParentEntity;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\NestedValueObject\ParentEntityMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NestedValueObjectTest extends DbIntegrationTest
{
    /**
     * @return IEntityMapper
     */
    protected function loadMapper()
    {
        return new ParentEntityMapper();
    }

    protected function getTestEntity()
    {
        $entity      = new ParentEntity();
        $entity->one = new LevelOne(new LevelTwo(new LevelThree('foobar')));

        return $entity;
    }

    public function testPersist()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertTrue($entity->hasId());
        $this->assertSame(1, $entity->getId());

        $this->assertDatabaseDataSameAs([
                'parents' => [
                        ['id' => 1, 'one_two_three_value' => 'foobar']
                ]
        ]);
    }

    public function testLoad()
    {
        $entity = $this->getTestEntity();

        $this->repo->save($entity);

        $this->assertEquals($entity, $this->repo->get(1));
        $this->assertSame('foobar', $this->repo->get(1)->one->two->three->val);
    }
}