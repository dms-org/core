<?php

namespace Iddigital\Cms\Core\Tests\Module\Fixtures;

use Iddigital\Cms\Core\Auth\Permission;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Module\Module;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleWithActions extends Module
{
    /**
     * Defines the module.
     *
     * @param ModuleDefinition $module
     *
     * @return void
     */
    protected function define(ModuleDefinition $module)
    {
        $module->name('test-module-with-actions');

        $module->action('unparameterized-action-no-return')
                ->authorize('permission.name')
                ->handler(function () {

                });

        $module->action('unparameterized-action-with-return')
                ->authorize('permission.name')
                ->returns(TestDto::class)
                ->handler(function () {
                    return new TestDto();
                });

        $module->action('mapped-form-action')
                ->authorize(Permission::named('permission.one'))
                ->form(
                        Form::create()->section('Input', [
                                Field::name('data')->label('Data')->string()
                        ]),
                        function (array $input) {
                            return new TestDto($input['data']);
                        }
                )
                ->returns(TestDto::class)
                ->handler(function (TestDto $input) {
                    return new TestDto($input->data . '-handled');
                });

        $module->action('array-form-action')
                ->authorize(Permission::named('permission.one'))
                ->form(Form::create()->section('Input', [
                        Field::name('data')->label('Data')->string()
                ]))
                ->returns(TestDto::class)
                ->handler(function (ArrayDataObject $input) {
                    return new TestDto($input['data'] . '-handled');
                });


        $module->action('form-object-action')
                ->authorizeAll([
                        'permission.one',
                        'permission.two',
                ])
                ->form(new TestFormObject())
                ->returns(TestDto::class)
                ->handler(function (TestFormObject $input) {
                    return new TestDto($input->data . '-handled-object');
                });

        $module->action('staged-form-object-action')
                ->form(new TestStagedFormObject())
                ->returns(TestDto::class)
                ->handler(function (TestStagedFormObject $input) {
                    return new TestDto($input->data . '-handled-staged');
                });
    }
}