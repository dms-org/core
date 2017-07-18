<?php

namespace Dms\Core\Tests\Persistence\Db\Mapper;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\IValueObject;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\LoadingContext;
use Dms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Dms\Core\Persistence\Db\PersistenceContext;
use Dms\Core\Persistence\Db\Row;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ValueObjectMapperTest extends CmsTestCase
{
    /**
     * @var IEmbeddedObjectMapper
     */
    protected $mapper;

    public function setUp()
    {
        $this->mapper = $this->buildMapper();
    }

    /**
     * @return IEmbeddedObjectMapper
     */
    abstract protected function buildMapper();

    /**
     * @return array[]
     */
    abstract public function mapperTests();

    /**
     * @dataProvider mapperTests
     */
    public function testLoadsObject(array $rowData, IValueObject $expectedObject)
    {
        $row = new Row($this->mapper->getDefinition()->getTable(), $rowData);

        $actualObject = $this->mapper->load(
                new LoadingContext($this->getMockForAbstractClass(IConnection::class)),
                $row
        );

        $this->assertEquals($expectedObject, $actualObject);
    }

    /**
     * @dataProvider mapperTests
     */
    public function testPersistsObject(array $expectedRowData, IValueObject $object)
    {
        $row = new Row($this->mapper->getDefinition()->getTable(), []);

        /** @var PersistenceContext $context */
        $context = $this->getMockBuilder(PersistenceContext::class)->disableOriginalConstructor()->getMock();
        $this->mapper->persistAllToRowsBeforeParent(
                $context,
                [$object],
                [$row]
        );
        $this->mapper->persistToRow(
                $context,
                $object,
                $row
        );
        $this->mapper->persistAllToRowsAfterParent(
                $context,
                [$object],
                [$row]
        );

        $this->assertEquals($expectedRowData, $row->getColumnData());
    }

}