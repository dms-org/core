<?php

namespace Iddigital\Cms\Core\Tests\Module\Mapping;

use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\CustomStagedFormDtoMapping;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Tests\Module\Mapping\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomStagedFormDtoMappingTest extends StagedFormDtoMappingTest
{
    /**
     * @return IStagedFormDtoMapping
     */
    protected function mapping()
    {
        return new CustomStagedFormDtoMapping(
                $this->expectedForm(),
                TestDto::class,
                function (array $data) {
                    return TestDto::from($data['field']);
                }
        );
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
        return TestDto::class;
    }

    /**
     * @return array[]
     */
    public function mappingTests()
    {
        return [
                [['field' => 0], TestDto::from(false)],
                [['field' => 1], TestDto::from(true)],
        ];
    }
}