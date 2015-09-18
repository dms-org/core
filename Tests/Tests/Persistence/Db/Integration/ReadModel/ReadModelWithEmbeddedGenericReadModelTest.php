<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded\GenericLabelReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded\ReadModelWithLabel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Embedded\ReadModelWithLabelRepository;

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
        $this->db->setData([
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