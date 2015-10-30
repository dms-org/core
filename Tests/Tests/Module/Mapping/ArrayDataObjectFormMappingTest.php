<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;

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