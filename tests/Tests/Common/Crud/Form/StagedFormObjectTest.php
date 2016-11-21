<?php

namespace Dms\Core\Tests\Common\Crud\Form;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;
use Dms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestFormObject;
use Dms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity as AnotherTestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormObjectTest extends CmsTestCase
{
    public function testNew()
    {
        $dataSource = TestEntity::getTestCollection();
        $form       = new TestFormObject($dataSource);

        $this->assertSame($dataSource, $form->getDataSource());
        $this->assertSame(null, $form->getObject());
        $this->assertSame(null, $form->string);
        $this->assertSame(2, $form->getAmountOfStages());
        $this->assertEquals(ObjectForm::build($dataSource), $form->getFirstForm());

        $this->assertEquals(
                Form::create()->section('Data', [
                    Field::name('string')->label('String')->string()->required(),
                ])->build(),
                $form->getFormForStage(2, [IObjectAction::OBJECT_FIELD_NAME => 2])
        );
    }

    public function testInvalidDataSource()
    {
        $this->setExpectedException(TypeMismatchException::class);

        new TestFormObject(AnotherTestEntity::collection());
    }

    public function testSubmitFirstStage()
    {
        $form = new TestFormObject(TestEntity::getTestCollection());

        $form = $form->submitFirstStage([IObjectAction::OBJECT_FIELD_NAME => 2]);

        $this->assertInstanceOf(TestFormObject::class, $form);
        $this->assertEquals(TestEntity::withId(2), $form->getObject());
        $this->assertSame(null, $form->string);
        $this->assertSame(1, $form->getAmountOfStages());
        $this->assertSame(['string'], $form->getFirstForm()->getFieldNames());

        $form->submit(['string' => 'abc']);

        $this->assertEquals(TestEntity::withId(2), $form->getObject());
        $this->assertSame('abc', $form->string);
    }
}