<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\ReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Properties\TypesReadModel;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\Properties\TypesReadModelRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\ValueObject\ReadModelWithValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\ReadModel\Fixtures\ValueObject\ReadModelWithValueObjectRepository;

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