<?php

namespace Dms\Core\Tests\Form\Object\Stage\Fixtures;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Form\Stage\IndependentFormStage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfIntsStagedFormTest extends CmsTestCase
{
    /**
     * @var ArrayOfIntsStagedForm
     */
    protected $form;

    public function setUp(): void
    {
        $this->form = new ArrayOfIntsStagedForm();
    }

    public function testNewForm()
    {
        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);

        $this->assertSame(2, $this->form->getAmountOfStages());
        $this->assertInstanceOf(IndependentFormStage::class, $this->form->getFirstStage());
        $this->assertCount(1, $this->form->getFirstStage()->loadForm()->getFields());
        $this->assertCount(1, $this->form->getFollowingStages());
        $this->assertSame($this->form->getFirstStage(), $this->form->getStage(1));
    }

    public function testProcess()
    {
        $this->assertEquals([
                'length' => 3,
                'ints'   => [1, 2, 4],
        ], $this->form->process([
                'length' => ' 3',
                'ints'   => ['1', '2', '4'],
        ]));

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);
    }

    public function testUnprocess()
    {
        $this->assertEquals([
                'length' => 3,
                'ints'   => [1, 2, 4],
        ], $this->form->unprocess([
                'length' => 3,
                'ints'   => [1, 2, 4],
        ]));

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);
    }

    public function testGetSecondStage()
    {
        $form = $this->form->getFormForStage(2, ['length' => ' 3']);

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);
        $this->assertSame(3, $form->getField('ints')->getType()->get(ArrayOfType::ATTR_EXACT_ELEMENTS));
    }

    public function testSubmit()
    {
        $this->assertSame($this->form, $this->form->submit([
                'length' => ' 3',
                'ints'   => ['1', '2', '4'],
        ]));

        $this->assertSame(3, $this->form->length);
        $this->assertSame([1, 2, 4], $this->form->ints);
    }

    public function testSubmitNew()
    {
        $submitted = $this->form->submitNew([
                'length' => ' 3',
                'ints'   => ['1', '2', '4'],
        ]);

        $this->assertNotSame($this->form, $submitted);

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);

        $this->assertSame(3, $submitted->length);
        $this->assertSame([1, 2, 4], $submitted->ints);
    }

    public function testInvalidSubmission()
    {
        $this->expectException(InvalidFormSubmissionException::class);

        $this->form->submit([
                'length' => '1',
                'ints'   => ['1', '2', '4'],
        ]);
    }

    public function testInvalidSubmissionWithSubmitNew()
    {
        $this->expectException(InvalidFormSubmissionException::class);

        $this->form->submitNew([
                'length' => '1',
                'ints'   => ['1', '2', '4'],
        ]);
    }

    public function testSubmitFirstStage()
    {
        $submitted = $this->form->submitFirstStage([
                'length' => ' 5 ',
        ]);

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);

        $this->assertSame(5, $submitted->length);
        $this->assertSame(null, $submitted->ints);

        $this->assertCount(1, $submitted->getAllStages());
        $this->assertInstanceOf(IndependentFormStage::class, $submitted->getStage(1));
        $this->assertSame(5, $submitted->getFirstForm()->getField('ints')->getType()->get(ArrayOfType::ATTR_EXACT_ELEMENTS));

        $this->assertThrows(function () {
            $this->form->submitFirstStage(['length' => 'abc']);
        }, InvalidFormSubmissionException::class);
    }

    public function testWithSubmittedFirstStage()
    {
        $submitted = $this->form->withSubmittedFirstStage([
                'length' => 3,
        ]);

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);

        $this->assertSame(3, $submitted->length);
        $this->assertSame(null, $submitted->ints);

        $this->assertCount(1, $submitted->getAllStages());
        $this->assertInstanceOf(IndependentFormStage::class, $submitted->getStage(1));
        $this->assertSame(3, $submitted->getFirstForm()->getField('ints')->getType()->get(ArrayOfType::ATTR_EXACT_ELEMENTS));

        $this->assertThrows(function () {
            $this->form->withSubmittedFirstStage(['length' => 'abc']);
        }, InvalidArgumentException::class);

        $submitted->submit([
                'ints'   => ['1', '2', '4'],
        ]);

        $this->assertNull($this->form->length);
        $this->assertNull($this->form->ints);

        $this->assertSame(3, $submitted->length);
        $this->assertSame([1, 2, 4], $submitted->ints);
    }
}