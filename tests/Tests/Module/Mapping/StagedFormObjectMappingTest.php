<?php

namespace Dms\Core\Tests\Module\Mapping;

use Dms\Core\Form\IForm;
use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Module\Mapping\StagedFormObjectMapping;
use Dms\Core\Tests\Form\Object\Stage\Fixtures\ArrayOfIntsStagedForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StagedFormObjectMappingTest extends StagedFormDtoMappingTest
{
    /**
     * @return IStagedFormDtoMapping
     */
    protected function mapping()
    {
        return new StagedFormObjectMapping(new ArrayOfIntsStagedForm());
    }

    /**
     * @return IForm
     */
    protected function expectedForm()
    {
        return new ArrayOfIntsStagedForm();
    }

    /**
     * @return string
     */
    protected function expectedDtoType()
    {
        return ArrayOfIntsStagedForm::class;
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [['length' => '3', 'ints' => [1, '2', 3]], (new ArrayOfIntsStagedForm())->submit(['length' => 3, 'ints' => [1, 2, 3]])],
        ];
    }
}