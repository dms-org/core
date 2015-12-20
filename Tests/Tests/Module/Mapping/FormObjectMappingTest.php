<?php

namespace Dms\Core\Tests\Module\Mapping;

use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Module\Mapping\FormObjectMapping;
use Dms\Core\Form\IForm;
use Dms\Core\Tests\Form\Object\Fixtures\ArrayOfInts;

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