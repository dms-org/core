<?php

namespace Dms\Core\Tests\Form;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\ConflictingFieldNameException;
use Dms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormBuilderTest extends FormBuilderTestBase
{
    public function testSimpleForm()
    {
        $form = Form::create()
                ->section('Details', [
                        Field::name('first_name')->label('First Name')->string()->required()->maxLength(50),
                        Field::name('middle_name')->label('Middle Name')->string()->maxLength(50),
                        Field::name('last_name')->label('Last Name')->string()->required()->maxLength(50),
                ])
                ->build();

        $this->assertSame(
                ['first_name' => 'Test', 'middle_name' => null, 'last_name' => 'Dummy'],
                $form->process(['first_name' => 'Test', 'last_name' => 'Dummy'])
        );
    }

    public function testConflictingFieldNames()
    {
        $this->setExpectedException(ConflictingFieldNameException::class);

        Form::create()
                ->section('One', [
                        Field::name('abc')->label('Abc')->string(),
                        Field::name('hello')->label('Hi')->int(),
                ])
                ->section('Two', [
                        Field::name('abc')->label('Other')->decimal(),
                ])
                ->build();
    }

    public function testMatchingFields()
    {
        $form = Form::create()
                ->section('Password', [
                        Field::name('password')->label('Password')->string()->required(),
                        Field::name('password_confirm')->label('Confirm Password')->string()->required(),
                ])
                ->fieldsMatch('password', 'password_confirm')
                ->build();

        $this->assertProcesses(['password' => 'foo', 'password_confirm' => 'foo'], $form);
        $this->assertInvalidSubmission(['password' => 'foo', 'password_confirm' => 'bar'], $form);
    }

    /**
     * @return Form
     */
    protected function comparisonNumbersForm()
    {
        return Form::create()
                ->section('Numbers', [
                        Field::name('first')->label('First')->int(),
                        Field::name('second')->label('Second')->int(),
                ]);
    }

    public function testFieldLessThanAnother()
    {
        $form = $this->comparisonNumbersForm()
                ->fieldLessThanAnother('first', 'second')
                ->build();

        $this->assertProcesses(['first' => null, 'second' => null], $form);
        $this->assertProcesses(['first' => 0, 'second' => 10], $form);
        $this->assertInvalidSubmission(['first' => 0, 'second' => 0], $form);
        $this->assertInvalidSubmission(['first' => 10, 'second' => 0], $form);
    }

    public function testFieldLessThanOrEqualAnother()
    {
        $form = $this->comparisonNumbersForm()
                ->fieldLessThanOrEqualAnother('first', 'second')
                ->build();

        $this->assertProcesses(['first' => null, 'second' => null], $form);
        $this->assertProcesses(['first' => 0, 'second' => 10], $form);
        $this->assertProcesses(['first' => 0, 'second' => 0], $form);
        $this->assertInvalidSubmission(['first' => 10, 'second' => 0], $form);
    }

    public function testFieldGreaterThanAnother()
    {
        $form = $this->comparisonNumbersForm()
                ->fieldGreaterThanAnother('first', 'second')
                ->build();

        $this->assertProcesses(['first' => null, 'second' => null], $form);
        $this->assertProcesses(['first' => 10, 'second' => 0], $form);
        $this->assertInvalidSubmission(['first' => 0, 'second' => 0], $form);
        $this->assertInvalidSubmission(['first' => 0, 'second' => 10], $form);
    }

    public function testFieldGreaterThanOrEqualAnother()
    {
        $form = $this->comparisonNumbersForm()
                ->fieldGreaterThanOrEqualAnother('first', 'second')
                ->build();

        $this->assertProcesses(['first' => null, 'second' => null], $form);
        $this->assertProcesses(['first' => 10, 'second' => 0], $form);
        $this->assertProcesses(['first' => 0, 'second' => 0], $form);
        $this->assertInvalidSubmission(['first' => 0, 'second' => 10], $form);
    }

    public function testMap()
    {
        $form = Form::create()
                ->section('Data', [
                        Field::name('data')->label('Data')->string()->required(),
                ])
                ->map(function ($i) {
                    $i['data'] .= 'foo';

                    return $i;
                }, function ($i) {
                    $i['data'] = substr($i['data'], 0, -3);

                    return $i;
                })
                ->build();

        $this->assertSame(['data' => 'barfoo'], $form->process(['data' => 'bar']));
    }

    public function testEmbeddedForm()
    {
        $embeddedForm = Form::create()
                ->section('Embedded', [
                        Field::name('inner1')->label('Inner 1')->int(),
                        Field::name('inner2')->label('Inner 2')->int(),
                ])
                ->fieldsMatch('inner1', 'inner2')
                ->build();

        $form = Form::create()
                ->section('Data', [
                        Field::name('data')->label('Data')->string()->required(),
                ])
                ->embed($embeddedForm)
                ->build();

        $expected = Form::create()
                ->section('Data', [
                        Field::name('data')->label('Data')->string()->required(),
                ])
                ->section('Embedded', [
                        Field::name('inner1')->label('Inner 1')->int(),
                        Field::name('inner2')->label('Inner 2')->int(),
                ])
                ->fieldsMatch('inner1', 'inner2')
                ->build();


        $this->assertEquals($expected, $form);
    }

    public function testContinuedSection()
    {
        $form = Form::create()
                ->section('Fields', [
                        Field::name('field1')->label('Field 1')->int(),
                ])
                ->continueSection([
                        Field::name('field2')->label('Field 2')->int(),
                ])
                ->build();


        $this->assertEquals(
                Form::create()
                        ->section('Fields', [
                                Field::name('field1')->label('Field 1')->int(),
                                Field::name('field2')->label('Field 2')->int(),
                        ])
                        ->build(),
                $form
        );
    }

    public function testContinuedSectionAsFirstSection()
    {
        // This must work due to staged forms continuing between stages
        $form = Form::create()
                ->continueSection([
                        Field::name('field2')->label('Field 2')->int(),
                ])
                ->build();

        $this->assertCount(1, $form->getSections());
        $this->assertEquals([Field::name('field2')->label('Field 2')->int()->build()], $form->getSections()[0]->getFields());
        $this->assertSame(true, $form->getSections()[0]->doesContinuePreviousSection());
        $this->assertSame('', $form->getSections()[0]->getTitle());
    }
}