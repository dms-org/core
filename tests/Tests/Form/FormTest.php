<?php

namespace Dms\Core\Tests\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Form\IStagedForm;

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
                'foo'  => null,
                'bar'  => 10.0
        ], $form->getInitialValues());
    }

    public function testWithInitialValues()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string()->value('abc'),
                        Field::name('foo')->label('Foo')->int(),
                        Field::name('bar')->label('Bar')->decimal()->value(10.0),
                ])
                ->build();

        $newForm = $form->withInitialValues([
                'name' => 'another',
                'foo'  => 10,
                'bar'  => -10.0
        ]);

        $this->assertNotEquals($form, $newForm);

        $this->assertSame([
                'name' => 'abc',
                'foo'  => null,
                'bar'  => 10.0
        ], $form->getInitialValues());

        $this->assertSame([
                'name' => 'another',
                'foo'  => 10,
                'bar'  => -10.0
        ], $newForm->getInitialValues());

        $this->assertThrows(function () use ($form) {
            $form->withInitialValues(['non-existent-field' => 'abc']);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($form) {
            $form->withInitialValues(['foo' => 'invalid-value-for-int']);
        }, InvalidArgumentException::class);
    }

    public function testValidateProcessedSubmission()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('name')->label('Name')->string()->value('abc'),
                        Field::name('foo')->label('Foo')->int()->required(),
                        Field::name('bar')->label('Bar')->decimal()->value(10.0),
                ])
                ->build();

        $form->validateProcessedValues([
                'name' => '123',
                'foo' => 123,
                'bar' => 0.0,
        ]);

        $form->validateProcessedValues([
                'name' => 'aaaa',
                'foo' => -343,
                'bar' => 0.1,
        ]);

        $form->validateProcessedValues([
                'name' => null,
                'foo' => 0,
                'bar' => null,
        ]);

        $this->assertThrows(function () use ($form) {
            $form->validateProcessedValues([
                    'name' => '',
                    'foo' => null, // Wrong
                    'bar' => 11.0,
            ]);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($form) {
            $form->validateProcessedValues([
                    'name' => 100, // Wrong
                    'foo' => 1,
                    'bar' => 11.0,
            ]);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () use ($form) {
            $form->validateProcessedValues([
                    'name' => '123',
                    'foo' => 123,
                    'bar' => 0.0,
                    'non-existent' => true // Wrong
            ]);
        }, InvalidArgumentException::class);
    }
}