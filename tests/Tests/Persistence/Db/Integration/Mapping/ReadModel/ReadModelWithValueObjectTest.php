<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Persistence\ReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties\TypesReadModel;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\Properties\TypesReadModelRepository;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObject\ReadModelWithValueObject;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel\Fixtures\ValueObject\ReadModelWithValueObjectRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModelWithValueObjectTest extends ReadModelRepositoryTest
{
    /**
     * @param IConnection $connection
     *
     * @return ReadModelRepository
     */
    protected function loadRepository(IConnection $connection)
    {
        return new ReadModelWithValueObjectRepository($connection);
    }

    public function testLoad()
    {
        $this->db->setData([
            'entities' => [
                    [
                            'id'                 => 1,
                            'name'               => 'some name',
                            'cents'              => 100,
                            'currency'           => 'AUD',
                            'prefix_cents'       => 497,
                            'prefix_currency'    => 'USD',
                            'has_nullable_money' => false,
                            'nullable_cents'     => null,
                            'nullable_currency'  => null,
                    ],
            ]
        ]);

        $this->assertEquals([
                new ReadModelWithValueObject(new EmbeddedMoneyObject(100, CurrencyEnum::aud())),
        ], $this->repo->getAll());

        $this->assertExecutedQueryTypes([
            'Load objects' => Select::class
        ]);

        $this->assertExecutedQueryNumber(1,
                Select::from($this->mapper->getPrimaryTable())
                        ->addRawColumn('cents')
                        ->addRawColumn('currency')
        );
    }
}