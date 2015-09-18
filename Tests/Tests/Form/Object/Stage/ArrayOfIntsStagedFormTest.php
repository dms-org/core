<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Stage\Fixtures;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfType;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfIntsStagedFormTest extends CmsTestCase
{
    /**
     * @var ArrayOfIntsStagedForm
     */
    protected $form;

    public function setUp()
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
        $this->assertSame(3, $form->getField('ints')->getType()->get(ArrayOfType::ATTR_MIN_ELEMENTS));
        $this->assertSame(3, $form->getField('ints')->getType()->get(ArrayOfType::ATTR_MAX_ELEMENTS));
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

    public function testInvalidSubmission()
    {
        $this->setExpectedException(InvalidFormSubmissionException::class);

        $this->form->submit([
                'length' => '1',
                'ints'   => ['1', '2', '4'],
        ]);
    }
}