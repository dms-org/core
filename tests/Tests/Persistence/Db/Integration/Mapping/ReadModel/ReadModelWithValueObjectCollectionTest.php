<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObjectCollection\EmbeddedEmailAddress;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObjectCollection\ReadModelWithValueObjectCollection;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObjectCollection\ReadModelWithValueObjectCollectionRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithValueObjectCollectionTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithValueObjectCollectionRepository($connection);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1]
                ],
                'emails'   => [
                        ['id' => 1, 'entity_id' => 1, 'email' => 'test@foo.com'],
                        ['id' => 2, 'entity_id' => 1, 'email' => 'gmail@foo.com'],
                        ['id' => 3, 'entity_id' => 1, 'email' => 'abc@foo.com'],
                ]
        ]);

        $this->assertEquals([
                new ReadModelWithValueObjectCollection([
                        new EmbeddedEmailAddress('test@foo.com'),
                        new EmbeddedEmailAddress('gmail@foo.com'),
                        new EmbeddedEmailAddress('abc@foo.com'),
                ])
        ], $this->repo->getAll());
    }
}