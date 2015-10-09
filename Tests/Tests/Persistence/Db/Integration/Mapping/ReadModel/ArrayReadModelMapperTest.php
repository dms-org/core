<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\ReadModel;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Mapping\ReadModel\ArrayReadModelMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
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
    protected $mapper;

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

        $this->mapper = new ArrayReadModelMapper($this->orm, $this->orm->findEntityMapper(EntityWithValueObject::class), [
                'name-index'             => 'name',
                'sub-object-prop'        => 'money.cents',
                'sub-object-prop-dup'    => 'money.cents',
                'sub-object'             => 'money',
                'nested-sub-object-prop' => 'prefixedMoney.currency.value',
        ]);
    }

    public function testFoo()
    {

    }
}