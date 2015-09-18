<?php

namespace Iddigital\Cms\Core\Tests\Form;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Type\StringType;
use Iddigital\Cms\Core\Form\Stage\DependentFormStage;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormBuilderTest extends FormBuilderTestBase
{
    public function testIndependentStagedForm()
    {
        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first_name')->label('First Name')->string()->required()
                        ])
        )->then(
                Form::create()
                        ->section('Second Stage', [
                                Field::name('last_name')->label('Last Name')->string()->required()
                        ])
        )->build();

        $this->assertSame(2, $form->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(2));

        $this->assertSame($form->getFirstStage(), $form->getStage(1));
        $this->assertSame([$form->getStage(2)], $form->getFollowingStages());

        $this->assertEquals(['first_name' => 'Joe'], $form->getFirstStage()->loadForm()->process(['first_name' => 'Joe']));
        $this->assertEquals(['last_name' => 'Joe'], $form->getStage(2)->loadForm()->process(['last_name' => 'Joe']));
    }

    public function testDependentFormStage()
    {
        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first_name')->label('First Name')->string()->required()
                        ])
        )->then(function (array $data) {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('last_name')->label('Last Name')->string()->required()->maxLength(strlen($data['first_name']))
                    ]);
        })->build();

        $this->assertSame(2, $form->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
        $this->assertInstanceOf(DependentFormStage::class, $form->getStage(2));
        $this->assertSame(
                strlen('foobar'),
                $form->getStage(2)
                        ->loadForm(['first_name' => 'foobar'])
                        ->getField('last_name')
                        ->getType()
                        ->get(StringType::ATTR_MAX_LENGTH)
        );
    }

    public function testIndependentFormStageFromClosureWithNoRequiredParameters()
    {

        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first_name')->label('First Name')->string()->required()
                        ])
        )->then(function (array $data = null) {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('last_name')->label('Last Name')->string()->required()
                    ]);
        })->build();

        $this->assertSame(2, $form->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(2));
    }

    public function testIndependentFormStageFromClosureWithNoParameters()
    {

        $form = StagedForm::begin(
                Form::create()
                        ->section('First Stage', [
                                Field::name('first_name')->label('First Name')->string()->required()
                        ])
        )->then(function () {
            return Form::create()
                    ->section('Second Stage', [
                            Field::name('last_name')->label('Last Name')->string()->required()
                    ]);
        })->build();

        $this->assertSame(2, $form->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(2));
    }

    public function testInvalidNumberOfParameters()
    {
        $this->assertThrows(function () {
            StagedForm::begin(
                    Form::create()->build()
            )->then(function (array $foo, $bar) {

            });
        }, InvalidArgumentException::class);
    }

    public function testDependentFirstStageParameterThrows()
    {
        $this->assertThrows(function () {
            StagedForm::begin(function (array $foo) {

            });
        }, InvalidArgumentException::class);
    }

    public function testIndependentFirstStageWithoutParameter()
    {
        $form = StagedForm::begin(function () {
            return Form::create()->build();
        })->build();

        $this->assertInstanceOf(IndependentFormStage::class, $form->getStage(1));
    }
}