<?php

namespace Iddigital\Cms\Core\Tests\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormTest extends FormBuilderTestBase
{
    private function buildTestStagedForm()
    {
        return StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('fields')->label('# Fields')->int()->required()->min(0)
                        ])
        )->then(function (array $data) {
            $fields = [];

            for ($i = 1; $i <= $data['fields']; $i++) {
                $fields[] = Field::name('field_' . $i)->label('Field #' . $i)
                        ->string()
                        ->required()
                        ->map(function ($data) {
                            return strtoupper($data);
                        }, function ($data) {
                            return strtolower($data);
                        }, Type::string());
            }

            return Form::create()->section('Fields', $fields);
        })->build();
    }

    public function testGetFormForStage()
    {
        $form = $this->buildTestStagedForm();

        $this->assertCount(3, $form->getFormForStage(2, ['fields' => ' 3'])->getFields());
    }

    public function testProcess()
    {
        $form = $this->buildTestStagedForm();

        $this->assertSame(
                ['fields' => 0],
                $form->process(['fields' => '0'])
        );

        $this->assertSame(
                ['fields' => 3, 'field_1' => 'FOO', 'field_2' => 'BAR', 'field_3' => 'BAZ'],
                $form->process(['fields' => '3', 'field_1' => 'foo', 'field_2' => 'bar', 'field_3' => 'baz'])
        );
    }

    public function testUnprocess()
    {
        $form = $this->buildTestStagedForm();

        $this->assertSame(
                ['fields' => 0],
                $form->unprocess(['fields' => 0])
        );

        $this->assertSame(
                ['fields' => 3, 'field_1' => 'foo', 'field_2' => 'bar', 'field_3' => 'baz'],
                $form->unprocess(['fields' => 3, 'field_1' => 'FOO', 'field_2' => 'BAR', 'field_3' => 'BAZ'])
        );
    }

    public function testGetStageFormWithThreeStages()
    {
        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first')->label('Input')->string()
                        ])
        )->then(
                Form::create()
                        ->section('Second Stage', [
                                Field::name('second')->label('Input')->string()
                        ])
        )->then(function (array $data) {
            return Form::create()
                    ->section('Third Stage', [
                            Field::name($data['first'])->label($data['second'])->string()
                    ]);
        })->build();

        $this->assertEquals(
                Field::name('foo')->label('bar')->string()->build(),
                $form->getFormForStage(3, ['first' => 'foo', 'second' => 'bar'])->getField('foo')
        );

        $this->assertSame([$form->getFirstStage(), $form->getStage(2), $form->getStage(3)], $form->getAllStages());
        $this->assertSame([], $form->getRequiredFieldNamesForStage(1));
        $this->assertSame([], $form->getRequiredFieldNamesForStage(2));
        $this->assertSame(['first', 'second'], $form->getRequiredFieldNamesForStage(3));
        $this->assertSame($form->getStage(1), $form->getStageWithFieldName('first'));
        $this->assertSame($form->getStage(2), $form->getStageWithFieldName('second'));
        $this->assertThrows(function () use ($form) {
            $form->getStageWithFieldName('invalid-field');
        }, InvalidArgumentException::class);
    }

    public function testFormDependingOnSpecificFields()
    {
        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first')->label('String')->string(),
                                Field::name('count')->label('Number')->int()->required(),
                        ])
        )->thenDependingOn(['count'], function (array $data) {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('field: ' . $data['count'])->label('Label')->string()
                    ]);
        })->build();


        $this->assertSame([], $form->getRequiredFieldNamesForStage(1));
        $this->assertSame(['count'], $form->getRequiredFieldNamesForStage(2));
        $this->assertSame(['count'], $form->getRequiredFieldNamesForStage(2));


        $this->assertEquals(
                Field::name('field: 3')->label('Label')->string()->build(),
                $form->getFormForStage(2, ['count' => '3'])->getField('field: 3')
        );
    }
}