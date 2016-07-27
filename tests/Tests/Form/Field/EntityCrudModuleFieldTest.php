<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Auth\IAdmin;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleCrudModule;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\Simple\SimpleEntity;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityCrudModuleFieldTest extends FieldBuilderTestBase
{
    /**
     * @var ICrudModule
     */
    protected $module;

    public function setUp()
    {
        $this->module = new SimpleCrudModule(SimpleEntity::collection([
            new SimpleEntity(1, 'abc'),
            new SimpleEntity(2, '123'),
            new SimpleEntity(3, 'xyz'),
        ]), new MockAuthSystem($this->getMockForAbstractClass(IAdmin::class), $this));
    }

    public function field()
    {
        return Field::create()->name('data')->label('Data')
            ->module($this->module)
            ->value($this->module->getDataSource())
            ->build();
    }

    public function testInitialData()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $this->assertSame([
            [$id => 1, 'data' => 'abc'],
            [$id => 2, 'data' => '123'],
            [$id => 3, 'data' => 'xyz'],
        ], $this->field()->getUnprocessedInitialValue());
    }

    public function testProcessExistingEntities()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $return = $this->field()->process([
            [$id => 1, 'data' => 'edited'],
            [$id => 2, 'data' => '123456'],
            [$id => 3, 'data' => 'abc'],
        ]);

        $this->assertEquals([
            new SimpleEntity(1, 'edited'),
            new SimpleEntity(2, '123456'),
            new SimpleEntity(3, 'abc'),
        ], $this->module->getDataSource()->getAll());

        $this->assertSame($return, $this->module->getDataSource());
    }

    public function testCreateNewEntities()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $this->field()->process([
            [$id => 1, 'data' => 'abc'],
            [$id => 2, 'data' => '123'],
            [$id => 3, 'data' => 'xyz'],
            [$id => null, 'data' => 'new-entity'],
        ]);

        $this->assertEquals([
            new SimpleEntity(1, 'abc'),
            new SimpleEntity(2, '123'),
            new SimpleEntity(3, 'xyz'),
            new SimpleEntity(null, 'new-entity'),
        ], $this->module->getDataSource()->getAll());
    }

    public function testDeleteAll()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $this->field()->process([]);

        $this->assertEquals([], $this->module->getDataSource()->getAll());
    }
}