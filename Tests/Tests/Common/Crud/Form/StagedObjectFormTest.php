<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Form;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestObjectForm;
use Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator\Fixtures\TestEntity as AnotherTestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedObjectFormTest extends CmsTestCase
{
    public function testNew()
    {
        $dataSource = TestEntity::getTestCollection();
        $form       = new TestObjectForm($dataSource);

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

        new TestObjectForm(AnotherTestEntity::collection());
    }

    public function testSubmitFirstStage()
    {
        $form = new TestObjectForm(TestEntity::getTestCollection());

        $form = $form->submitFirstStage([IObjectAction::OBJECT_FIELD_NAME => 2]);

        $this->assertInstanceOf(TestObjectForm::class, $form);
        $this->assertEquals(TestEntity::withId(2), $form->getObject());
        $this->assertSame(null, $form->string);
        $this->assertSame(1, $form->getAmountOfStages());
        $this->assertSame(['string'], $form->getFirstForm()->getFieldNames());

        $form->submit(['string' => 'abc']);

        $this->assertEquals(TestEntity::withId(2), $form->getObject());
        $this->assertSame('abc', $form->string);
    }
}