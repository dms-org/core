<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded\GenericLabelReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded\ReadModelWithLabel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Embedded\ReadModelWithLabelRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithEmbeddedGenericReadModelTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithLabelRepository($connection);
    }

    public function testLoad()
    {
        $this->setDataInDb([
                'entities' => [
                        ['id' => 1, 'title' => 'One'],
                        ['id' => 2, 'title' => 'Two'],
                        ['id' => 3, 'title' => 'Three'],
                ]
        ]);

        $this->assertEquals([
                new ReadModelWithLabel(new GenericLabelReadModel(1, 'One')),
                new ReadModelWithLabel(new GenericLabelReadModel(2, 'Two')),
                new ReadModelWithLabel(new GenericLabelReadModel(3, 'Three')),
        ], $this->repo->getAll());

        $this->assertExecutedQueryTypes([
                'Load objects' => Select::class
        ]);

        $this->assertExecutedQueryNumber(1,
                Select::from($this->mapper->getPrimaryTable())
                        ->addRawColumn('id')
                        ->addRawColumn('title')
        );
    }
}