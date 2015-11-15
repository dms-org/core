<?php

namespace Iddigital\Cms\Core\Tests\Module\Action\Fixtures;

use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\IDataTransferObject;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Action\SelfHandlingParameterizedAction;
use Iddigital\Cms\Core\Module\IStagedFormDtoMapping;
use Iddigital\Cms\Core\Module\Mapping\ArrayDataObjectFormMapping;
use Iddigital\Cms\Core\Tests\Module\Fixtures\TestDto;

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
    protected function name()
    {
        return 'test-parameterized-action';
    }

    /**
     * Gets the required permissions.
     *
     * @return IPermission[]
     */
    protected function permissions()
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
    protected function formMapping()
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
    protected function returnDtoType()
    {
        return TestDto::class;
    }

    /**
     * Runs the action handler.
     *
     * @param $data
     *
     * @returns TestDto
     * @throws TypeMismatchException
     */
    public function runHandler(IDataTransferObject $data)
    {
        /** @var ArrayDataObject $data */
        return new TestDto(strtoupper($data['string']));
    }

}