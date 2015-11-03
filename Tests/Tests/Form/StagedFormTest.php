<?php

namespace Iddigital\Cms\Core\Tests\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Stage\DependentFormStage;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;
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

        $this->assertSame([], $form->getRequiredFieldGroupedByStagesForStage(1));
        $this->assertSame([], $form->getRequiredFieldGroupedByStagesForStage(2));
        $this->assertSame([1 => ['first'], 2 => ['second']], $form->getRequiredFieldGroupedByStagesForStage(3));

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


        $this->assertSame([], $form->getRequiredFieldGroupedByStagesForStage(1));
        $this->assertSame([1 => ['count']], $form->getRequiredFieldGroupedByStagesForStage(2));


        $this->assertEquals(
                Field::name('field: 3')->label('Label')->string()->build(),
                $form->getFormForStage(2, ['count' => '3'])->getField('field: 3')
        );

        $this->assertThrows(function () use ($form) {
            $form->getFormForStage(3, ['first' => 'abc']);
        }, InvalidArgumentException::class);
    }

    public function testFormDependentOnDependentFields()
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
                            Field::name('dependent')->label('Label (' . $data['count'] . ')')->string()->required()
                    ]);
        }, ['dependent'])->thenDependingOn(['dependent'], function (array $data) {
            return Form::create()
                    ->section('Third Stage', [
                            Field::name('field: ' . $data['dependent'])->label('Label')->string()
                    ]);
        })->build();


        $this->assertSame([], $form->getRequiredFieldGroupedByStagesForStage(1));
        $this->assertSame([1 => ['count']], $form->getRequiredFieldGroupedByStagesForStage(2));
        $this->assertSame([1 => ['count'], 2 => ['dependent']], $form->getRequiredFieldGroupedByStagesForStage(3));


        $this->assertEquals(
                Field::name('dependent')->label('Label (3)')->string()->required()->build(),
                $form->getFormForStage(2, ['count' => '3'])->getField('dependent')
        );

        $this->assertEquals(
                Field::name('field: abc')->label('Label')->string()->build(),
                $form->getFormForStage(3, ['count' => '3', 'dependent' => 'abc'])->getField('field: abc')
        );

        $this->assertThrows(function () use ($form) {
            $form->getFormForStage(3, ['dependent' => 'abc']);
        }, InvalidArgumentException::class);
    }

    public function testFormDependentOnAllFields()
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
                            Field::name('dependent')->label('Label (' . $data['count'] . ')')->string()->required()
                    ]);
        })->then(function (array $data) {
            return Form::create()
                    ->section('Third Stage', [
                            Field::name('field: ' . $data['dependent'])->label('Label')->string()
                    ]);
        })->build();


        $this->assertSame([], $form->getRequiredFieldGroupedByStagesForStage(1));
        $this->assertSame([1 => ['count']], $form->getRequiredFieldGroupedByStagesForStage(2));
        $this->assertSame([1 => ['first', 'count'], 2 => '*'], $form->getRequiredFieldGroupedByStagesForStage(3));


        $this->assertEquals(
                Field::name('dependent')->label('Label (3)')->string()->required()->build(),
                $form->getFormForStage(2, ['count' => '3'])->getField('dependent')
        );

        $this->assertEquals(
                Field::name('field: abc')->label('Label')->string()->build(),
                $form->getFormForStage(3, ['first' => 'abc', 'count' => '3', 'dependent' => 'abc'])->getField('field: abc')
        );

        $this->assertThrows(function () use ($form) {
            $form->getFormForStage(3, ['count' => '3', 'dependent' => 'abc']);
        }, InvalidArgumentException::class);
    }

    public function testThrowsExceptionForDependingOnFutureStage()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first')->label('String')->string(),
                        ])
        )->thenDependingOn(['count'], function (array $data) {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('dependent')->label('Label (' . $data['count'] . ')')->string()->required()
                    ]);
        })->then(
                Form::create()
                        ->section('Third Stage', [
                                Field::name('count')->label('Count')->string(),
                        ])
        )->build();
    }

    public function testThrowsExceptionForDependingOnNonExistentField()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first')->label('String')->string(),
                        ])
        )->thenDependingOn(['abc'], function (array $data) {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('dependent')->label('Label (' . $data['count'] . ')')->string()->required()
                    ]);
        })->build();
    }

    public function testWithSubmittedFirstStage()
    {
        $form = $this->buildTestStagedForm()->withSubmittedFirstStage([
                'fields' => 3
        ]);

        $this->assertCount(1, $form->getAllStages());

        $this->assertSame(
                ['field_1' => 'FOO', 'field_2' => 'BAR', 'field_3' => 'BAZ'],
                $form->process(['field_1' => 'foo', 'field_2' => 'bar', 'field_3' => 'baz'])
        );
    }

    public function testWithSubmittedFirstStageWithFollowingDepdendentStage()
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
                            Field::name('dependent')->label('Label (' . $data['count'] . ')')->string()->required()
                    ]);
        })->then(function (array $data) {
            return Form::create()
                    ->section('Third Stage', [
                            Field::name('field: ' . $data['first'] . ' - ' . $data['dependent'])->label('Label')->string()
                    ]);
        })->build();

        $form = $form->withSubmittedFirstStage([
            'first' => 'ABC',
            'count' => 123
        ]);

        $this->assertCount(2, $form->getAllStages());
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
        $this->assertInstanceOf(DependentFormStage::class, $form->getStage(2));

        $this->assertSame(
                ['field: ABC - bar'],
                $form->getFormForStage(2, ['dependent' => 'bar'])->getFieldNames()
        );

        $this->assertSame(
                ['dependent' => 'foo', 'field: ABC - foogit s' => 'bar'],
                $form->process(['dependent' => 'foo', 'field: ABC - foo' => 'bar'])
        );
    }
}