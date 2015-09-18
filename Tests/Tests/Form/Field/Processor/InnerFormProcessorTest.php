<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Processor\InnerFormProcessor;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerFormProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new InnerFormProcessor(Form::create()
                ->section('Section', [
                        Field::name('field')->label('Field')->int()->required()
                ])
                ->build()
        );
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::arrayOf(Type::mixed())->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
                [null, null],
                [['field' => '123'], ['field' => 123]],
                [['field' => '-456 '], ['field' => -456]],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
                [null, null],
                [['field' => 123], ['field' => 123]],
                [['field' => -456], ['field' => -456]],
        ];
    }
}