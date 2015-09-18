<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping;

use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\FormObjectMapping;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Tests\Form\Object\Fixtures\ArrayOfInts;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormObjectMappingTest extends StagedFormDtoMappingTest
{
    /**
     * @return IStagedFormDtoMapping
     */
    protected function mapping()
    {
        return new FormObjectMapping(ArrayOfInts::withLength(3));
    }

    /**
     * @return IForm
     */
    protected function expectedForm()
    {
        return ArrayOfInts::withLength(3)->asStagedForm();
    }

    /**
     * @return string
     */
    protected function expectedDtoType()
    {
        return ArrayOfInts::class;
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
            [['data' => [1, '2', 3]], ArrayOfInts::withLength(3)->submit(['data' => [1, 2, 3]])],
        ];
    }
}