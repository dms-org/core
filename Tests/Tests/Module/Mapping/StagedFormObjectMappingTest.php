<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping;

use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\StagedFormObjectMapping;
use Iddigital\Cms\Core\Tests\Form\Object\Stage\Fixtures\ArrayOfIntsStagedForm;

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