<?php

namespace Dms\Core\Tests\Module\Mapping;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Module\Mapping\ArrayDataObjectFormMapping;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayDataObjectFormMappingTest extends StagedFormDtoMappingTest
{
    /**
     * @return IStagedFormDtoMapping
     */
    protected function mapping()
    {
        return new ArrayDataObjectFormMapping($this->expectedForm());
    }

    /**
     * @return IStagedForm
     */
    protected function expectedForm()
    {
        return StagedForm::begin(
                Form::create()
                        ->section('Test', [
                                Field::create()->name('field')->label('Field')->bool()
                        ])
                        ->build()
        )->build();
    }

    /**
     * @return string
     */
    protected function expectedDtoType()
    {
        return ArrayDataObject::class;
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [['field' => 0], new ArrayDataObject(['field' => false])],
                [['field' => 1], new ArrayDataObject(['field' => true])],
        ];
    }
}