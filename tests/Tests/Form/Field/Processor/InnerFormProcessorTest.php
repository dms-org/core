<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Processor\InnerFormProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

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