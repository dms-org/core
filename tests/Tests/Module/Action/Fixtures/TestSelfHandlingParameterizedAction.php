<?php

namespace Dms\Core\Tests\Module\Action\Fixtures;

use Dms\Core\Auth\IPermission;
use Dms\Core\Auth\Permission;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\Object\ArrayDataObject;
use Dms\Core\Module\Action\SelfHandlingParameterizedAction;
use Dms\Core\Module\IStagedFormDtoMapping;
use Dms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Dms\Core\Tests\Module\Fixtures\TestDto;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestSelfHandlingParameterizedAction extends SelfHandlingParameterizedAction
{
    /**
     * Gets the action name.
     *
     * @return string
     */
    protected function name() : string
    {
        return 'test-parameterized-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions() : array
    {
        return [
                Permission::named('test-permission')
        ];
    }

    /**
     * Gets the action form mapping.
     *
     * @return IStagedFormDtoMapping
     */
    protected function formMapping() : IStagedFormDtoMapping
    {
        return new ArrayDataObjectFormMapping(
                Form::create()->section('Input', [
                        Field::name('string')->label('String')->string()->required()
                ])->build()->asStagedForm()
        );
    }

    /**
     * Gets the return dto type.
     *
     * @return string|null
     */
    protected function returnType()
    {
        return TestDto::class;
    }

    /**
     * Runs the action handler.
     *
     * @param object $data
     *
     * @returns TestDto
     * @throws TypeMismatchException
     */
    public function runHandler($data)
    {
        /** @var ArrayDataObject $data */
        return new TestDto(strtoupper($data['string']));
    }

}