<?php

namespace Dms\Core\Tests\Form\Binding;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Binding\Accessor\FieldPropertyAccessor;
use Dms\Core\Form\Binding\Accessor\GetterSetterMethodAccessor;
use Dms\Core\Form\Binding\FieldBinding;
use Dms\Core\Form\Binding\FormBinding;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Binding\IFormBinding;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IForm;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormBindingTest extends CmsTestCase
{
    /**
     * @var IForm
     */
    protected $form;

    /**
     * @var IFormBinding
     */
    protected $binding;

    public function setUp()
    {
        $this->form = Form::create()
            ->section('Input', [
                Field::name('string')->label('String')->string()->required(),
                Field::name('int')->label('Int')->int()->required(),
                Field::name('bool')->label('Bool')->bool()->required(),
            ])
            ->build();

        $this->binding = new FormBinding(
            $this->form,
            TestFormBoundClass::class,
            [
                new FieldBinding('string', new FieldPropertyAccessor(TestFormBoundClass::definition(), 'string')),
                new FieldBinding('int', new GetterSetterMethodAccessor(TestFormBoundClass::class, 'getInt', 'setInt')),
                new FieldBinding('bool', new FieldPropertyAccessor(TestFormBoundClass::definition(), 'bool')),
            ]
        );
    }

    public function testGetters()
    {
        $this->assertSame(TestFormBoundClass::class, $this->binding->getObjectType());
        $this->assertSame($this->form, $this->binding->getForm());

        $this->assertSame(true, $this->binding->hasFieldBinding('string'));
        $this->assertSame(true, $this->binding->hasFieldBinding('int'));
        $this->assertSame(false, $this->binding->hasFieldBinding('non-existent'));
        $this->assertInstanceOf(FieldPropertyAccessor::class, $this->binding->getFieldBinding('string')->getAccessor());
        $this->assertInstanceOf(GetterSetterMethodAccessor::class, $this->binding->getFieldBinding('int')->getAccessor());
        $this->assertThrows(function () {
            $this->binding->getFieldBinding('non-existent');
        }, InvalidArgumentException::class);

        $this->assertCount(3, $this->binding->getFieldBindings());
        $this->assertContainsOnlyInstancesOf(IFieldBinding::class, $this->binding->getFieldBindings());
        $this->assertSame(['string', 'int', 'bool'], array_keys($this->binding->getFieldBindings()));
    }

    public function testGetFormWithInitialValues()
    {
        $object = new TestFormBoundClass('foobar', 5, true);

        $form = $this->binding->getForm($object);

        $this->assertNotEquals($this->form, $form);
        $this->assertSame([
            'string' => 'foobar',
            'int'    => 5,
            'bool'   => true,
        ], $form->getInitialValues());
    }

    public function testBindSubmissionToObject()
    {
        $object = new TestFormBoundClass('foobar', 5, true);

        $this->binding->bindTo($object, [
            'string' => 'abc',
            'int'    => '123',
            'bool'   => '1',
        ]);

        $this->assertSame('abc', $object->string);
        $this->assertSame(123, $object->int);
        $this->assertSame(true, $object->bool);
    }

    public function testBindProcessedSubmissionToObject()
    {

        $object = new TestFormBoundClass('foobar', 5, true);

        $this->binding->bindProcessedTo($object, [
            'string' => 'abc',
            'int'    => 123,
            'bool'   => true,
        ]);

        $this->assertSame('abc', $object->string);
        $this->assertSame(123, $object->int);
        $this->assertSame(true, $object->bool);
    }

    public function testInvalidFormSubmission()
    {
        $this->setExpectedException(InvalidFormSubmissionException::class);

        $object = new TestFormBoundClass('foobar', 5, true);

        $this->binding->bindTo($object, [
            'string' => '1',
            'int'    => 'invalid-int',
            'bool'   => '1',
        ]);
    }

    public function testInvalidProcessedSubmission()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $object = new TestFormBoundClass('foobar', 5, true);

        $this->binding->bindProcessedTo($object, [
            'string' => '1',
            'int'    => 'invalid-int',
            'bool'   => '1',
        ]);
    }
}