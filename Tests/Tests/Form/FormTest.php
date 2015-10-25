<?php

namespace Iddigital\Cms\Core\Tests\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IStagedForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormTest extends FormBuilderTestBase
{
    public function testFieldNames()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $this->assertSame(
                ['name'],
                $form->getFieldNames()
        );

        $this->assertTrue($form->hasField('name'));
        $this->assertFalse($form->hasField('non-existent'));

        $this->assertInstanceOf(IField::class, $form->getField('name'));
        $this->assertThrows(function () use ($form) {
            $form->getField('non-existent');
        });
    }

    public function testProcessIgnoresExtraKeys()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $this->assertSame(
                ['name' => 'Test'],
                $form->process(['name' => 'Test', 'foo' => 'bar'])
        );
    }

    public function testProcessSetsMissingKeysToNull()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $this->assertSame(
                ['name' => null],
                $form->process([])
        );
    }

    public function testUnprocessThrowsOnMissingKeys()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $form->unprocess([]);
    }

    public function testUnprocessThrowsOnExtraKeys()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $form->unprocess(['name' => 'bar', 'foo' => 'bar']);
    }

    public function testUnprocessReordersKeys()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('first_name')->label('First Name')->string(),
                        Field::name('last_name')->label('First Name')->string(),
                ])
                ->build();

        $this->assertSame(
                ['first_name' => 'Joe', 'last_name' => 'Bar'],
                $form->unprocess(['last_name' => 'Bar', 'first_name' => 'Joe'])
        );
    }

    public function testAsStagedForm()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string(),
                ])
                ->build();

        $stagedForm = $form->asStagedForm();

        $this->assertInstanceOf(IStagedForm::class, $stagedForm);
        $this->assertSame(1, $stagedForm->getAmountOfStages());
        $this->assertSame($form, $stagedForm->getFirstStage()->loadForm());
        $this->assertSame([], $stagedForm->getFollowingStages());
    }

    public function testGetInitialValues()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string()->value('abc'),
                        Field::name('foo')->label('Foo')->int(),
                        Field::name('bar')->label('Bar')->decimal()->value(10.0),
                ])
                ->build();

        $this->assertSame([
            'name' => 'abc',
            'foo' => null,
            'bar' => 10.0
        ], $form->getInitialValues());
    }
}