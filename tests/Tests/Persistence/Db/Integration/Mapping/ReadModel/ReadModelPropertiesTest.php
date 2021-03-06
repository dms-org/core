<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties\TypesReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties\TypesReadModelRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelPropertiesTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new TypesReadModelRepository($connection);
    }

    public function testLoad()
    {
        $this->setDataInDb([
            'types' => [
                    [
                            'id'       => 1,
                            'null'     => null,
                            'int'      => 12,
                            'string'   => 'abc',
                            'bool'     => true,
                            'float'    => 123.4,
                            'date'     => '2000-01-01',
                            'time'     => '12:30:50',
                            'datetime' => '2010-03-04 12:34:56',
                    ],
                    [
                            'id'       => 2,
                            'null'     => null,
                            'int'      => -12,
                            'string'   => 'abc',
                            'bool'     => true,
                            'float'    => 10e4,
                            'date'     => '2000-05-01',
                            'time'     => '12:30:50',
                            'datetime' => '2010-03-04 12:34:56',
                    ],
                    [
                            'id'       => 3,
                            'null'     => null,
                            'int'      => 500,
                            'string'   => 'abc',
                            'bool'     => true,
                            'float'    => -123.4,
                            'date'     => '2001-01-01',
                            'time'     => '12:30:50',
                            'datetime' => '2010-03-04 12:34:56',
                    ],
            ]
        ]);

        $this->assertEquals([
                new TypesReadModel(12, 123.4, new \DateTimeImmutable('2000-01-01')),
                new TypesReadModel(-12, 10e4, new \DateTimeImmutable('2000-05-01')),
                new TypesReadModel(500, -123.4, new \DateTimeImmutable('2001-01-01')),
        ], $this->repo->getAll());

        $this->assertExecutedQueryTypes([
            'Load objects' => Select::class
        ]);

        $this->assertExecutedQueryNumber(1,
                Select::from($this->mapper->getPrimaryTable())
                        ->addRawColumn('int')
                        ->addRawColumn('float')
                        ->addRawColumn('date')
        );
    }
}