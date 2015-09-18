<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mapper;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\IValueObject;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Persistence\Db\LoadingContext;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\PersistenceContext;
use Iddigital\Cms\Core\Persistence\Db\Row;

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

        $this->mapper->persistToRow(
                new PersistenceContext(),
                $object,
                $row
        );

        $this->assertEquals($expectedRowData, $row->getColumnData());
    }

}