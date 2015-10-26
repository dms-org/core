<?php

namespace Iddigital\Cms\Core\Tests\Form\Binding;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Binding\Field\FieldPropertyBinding;
use Iddigital\Cms\Core\Form\Binding\Field\GetterSetterMethodBinding;
use Iddigital\Cms\Core\Form\Binding\FormBinding;
use Iddigital\Cms\Core\Form\Binding\IFieldBinding;
use Iddigital\Cms\Core\Form\Binding\IFormBinding;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

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
                        new FieldPropertyBinding('string', TestFormBoundClass::definition(), 'string'),
                        new GetterSetterMethodBinding('int', TestFormBoundClass::class, 'getInt', 'setInt'),
                        new FieldPropertyBinding('bool', TestFormBoundClass::definition(), 'bool'),
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
        $this->assertInstanceOf(FieldPropertyBinding::class, $this->binding->getFieldBinding('string'));
        $this->assertInstanceOf(GetterSetterMethodBinding::class, $this->binding->getFieldBinding('int'));
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
}