<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Form;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Form\Field\Type\EntityIdType;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping\Fixtures\TestEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectFormTest extends CmsTestCase
{
    public function testFormFactoryMethod()
    {
        $dataSource = TestEntity::getTestCollection();
        $form = ObjectForm::build($dataSource);

        $this->assertInstanceOf(IForm::class, $form);
        $this->assertCount(1, $form->getSections());
        $this->assertSame(true, $form->hasField(IObjectAction::OBJECT_FIELD_NAME));
        $this->assertSame([IObjectAction::OBJECT_FIELD_NAME], $form->getFieldNames());
        $this->assertInstanceOf(EntityIdType::class, $form->getField(IObjectAction::OBJECT_FIELD_NAME)->getType());
        $this->assertEquals(Type::mixed(), $form->getField(IObjectAction::OBJECT_FIELD_NAME)->getType()->getPhpTypeOfInput());
        $this->assertSame(true, $form->getField(IObjectAction::OBJECT_FIELD_NAME)->getType()->get(EntityIdType::ATTR_REQUIRED));
        $this->assertEquals(Type::int(), $form->getField(IObjectAction::OBJECT_FIELD_NAME)->getType()->getProcessedPhpType());
        $this->assertEquals(TestEntity::type(), $form->getField(IObjectAction::OBJECT_FIELD_NAME)->getProcessedType());

        $this->assertEquals(
                [IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(3)],
                $form->process([IObjectAction::OBJECT_FIELD_NAME => 3])
        );
    }

    public function testFormFactoryMethodWithValidationCallback()
    {
        $dataSource = TestEntity::getTestCollection();
        $form = ObjectForm::build($dataSource, function (TestEntity $entity) {
            return $entity->getId() >= 2;
        });

        $this->assertEquals(
                [IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(3)],
                $form->process([IObjectAction::OBJECT_FIELD_NAME => 3])
        );

        $this->assertEquals(
                [IObjectAction::OBJECT_FIELD_NAME => TestEntity::withId(2)],
                $form->process([IObjectAction::OBJECT_FIELD_NAME => 2])
        );

        $this->assertThrows(function () use ($form) {
            $form->process([IObjectAction::OBJECT_FIELD_NAME => 1]);
        }, InvalidFormSubmissionException::class);
    }
}