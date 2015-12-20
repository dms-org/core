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
}