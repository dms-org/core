<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModel;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModelMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Select;
use Iddigital\Cms\Core\Persistence\DbRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\CurrencyEnum;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EmbeddedMoneyObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObject;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\Fixtures\ValueObject\EntityWithValueObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayReadModelMapperTest extends DbIntegrationTest
{
    /**
     * @var ArrayReadModelMapper
     */
    protected $arrayMapper;

    /**
     * @var DbRepository
     */
    protected $arrayRepo;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return EntityWithValueObjectMapper::orm();
    }

    public function setUp()
    {
        parent::setUp();

        $this->arrayMapper = new ArrayReadModelMapper($this->orm, $this->orm->findEntityMapper(EntityWithValueObject::class), [
                'name-index'             => 'name',
                'sub-object-prop'        => 'money.cents',
                'sub-object'             => 'money',
                'nested-sub-object-prop' => 'prefixedMoney.currency.value',
        ]);

        $this->arrayRepo = new DbRepository($this->connection, $this->arrayMapper);
    }

    public function testLoadsArrayReadModels()
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
                                'has_nullable_money' => true,
                                'nullable_cents'     => 200,
                                'nullable_currency'  => 'AUD',
                        ],
                        [
                                'id'                 => 2,
                                'name'               => 'another name',
                                'cents'              => 500,
                                'currency'           => 'USD',
                                'prefix_cents'       => 100,
                                'prefix_currency'    => 'AUD',
                                'has_nullable_money' => false,
                                'nullable_cents'     => null,
                                'nullable_currency'  => null,
                        ],
                ]
        ]);


        $this->assertEquals([
                new ArrayReadModel([
                        'name-index'             => 'some name',
                        'sub-object-prop'        => 100,
                        'sub-object'             => new EmbeddedMoneyObject(100, CurrencyEnum::aud()),
                        'nested-sub-object-prop' => 'USD',
                ]),
                new ArrayReadModel([
                        'name-index'             => 'another name',
                        'sub-object-prop'        => 500,
                        'sub-object'             => new EmbeddedMoneyObject(500, CurrencyEnum::usd()),
                        'nested-sub-object-prop' => 'AUD',
                ]),
        ], $this->arrayRepo->getAll());

        $this->assertExecutedQueryTypes([
            'Load read models' => Select::class
        ]);

        $this->assertExecutedQueryNumber(1,
                Select::from($this->db->getTable('entities')->getStructure())
                        ->addRawColumn('name')
                        ->addRawColumn('cents')
                        ->addRawColumn('currency')
                        ->addRawColumn('prefix_currency')
        );
    }
}