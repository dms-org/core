<?php

namespace Iddigital\Cms\Core\Tests\Form\Stage;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Stage\IndependentFormStage;

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