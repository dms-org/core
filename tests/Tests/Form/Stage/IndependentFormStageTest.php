<?php

namespace Dms\Core\Tests\Form\Stage;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Stage\IndependentFormStage;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IndependentFormStageTest extends CmsTestCase
{
    public function testNewFormStage()
    {
        $form  = Form::create()->section('Foo', [
            Field::name('abc')->label('Abc')->string()
        ])->build();
        $stage = new IndependentFormStage($form);

        $this->assertFalse($stage->requiresPreviousSubmission());
        $this->assertFalse($stage->areAllFieldsRequired());
        $this->assertSame([], $stage->getRequiredFieldNames());
        $this->assertSame(['abc'], $stage->getDefinedFieldNames());
        $this->assertSame($form, $stage->loadForm());
        $this->assertSame($form, $stage->loadForm(['bbbb' => 'foo']));
    }
}