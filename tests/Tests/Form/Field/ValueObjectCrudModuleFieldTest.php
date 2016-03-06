<?php declare(strict_types = 1);

namespace Dms\Core\Tests\Form\Field;

use Dms\Core\Auth\IUser;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject\SimpleValueObject;
use Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject\SimpleValueObjectCrudModule;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ValueObjectCrudModuleFieldTest extends FieldBuilderTestBase
{
    /**
     * @var ICrudModule
     */
    protected $module;

    public function setUp()
    {
        $this->module = new SimpleValueObjectCrudModule(SimpleValueObject::collection([
            new SimpleValueObject('abc'),
            new SimpleValueObject('123'),
            new SimpleValueObject('xyz'),
        ]), new MockAuthSystem($this->getMockForAbstractClass(IUser::class)));
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
        $this->assertSame([
            ['data' => 'abc'],
            ['data' => '123'],
            ['data' => 'xyz'],
        ], $this->field()->getUnprocessedInitialValue());
    }

    public function testProcessExistingEntities()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $return = $this->field()->process([
            [$id => 0, 'data' => 'edited'],
            [$id => 1, 'data' => '123456'],
            [$id => 2, 'data' => 'abc'],
        ]);

        $this->assertEquals([
            new SimpleValueObject('edited'),
            new SimpleValueObject('123456'),
            new SimpleValueObject('abc'),
        ], $this->module->getDataSource()->getAll());

        $this->assertSame($return, $this->module->getDataSource());
    }

    public function testCreateNewEntities()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $this->field()->process([
            [$id => 0, 'data' => 'abc'],
            [$id => 1, 'data' => '123'],
            [$id => 2, 'data' => 'xyz'],
            [$id => null, 'data' => 'new-object'],
        ]);

        $this->assertEquals([
            new SimpleValueObject('abc'),
            new SimpleValueObject('123'),
            new SimpleValueObject('xyz'),
            new SimpleValueObject('new-object'),
        ], $this->module->getDataSource()->getAll());
    }

    public function testDeleteAll()
    {
        $id = IObjectAction::OBJECT_FIELD_NAME;

        $this->field()->process([]);

        $this->assertEquals([], $this->module->getDataSource()->getAll());
    }
}