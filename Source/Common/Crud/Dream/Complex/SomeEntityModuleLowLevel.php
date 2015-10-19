<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Table\Builder\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SomeEntityModuleLowLevel extends CrudModule
{
    const PERMISSION_CREATE = 'some-entity.create';
    const PERMISSION_EDIT = 'some-entity.edit';
    const PERMISSION_DELETE = 'some-entity.delete';

    protected function define(ModuleDefinition $module)
    {
        $module->name('some-entity');
        $module->label('Some Entity', 'Some Entities');
    }

    protected function loadRepository(IConnection $connection)
    {
        return new ProductRepository($connection);
    }

    protected function actions(ModuleDefinition $actions)
    {
        $actions->action('create')
                ->authorize(self::PERMISSION_CREATE)
                ->form(Form::create()
                        ->section('Details', [
                                Field::name('data')->label('Data')->string()->required()->maxLength(500)
                        ])
                )
                ->handler(function (array $input) {
                    $this->repository->save(new Product(null, $input['data']));
                });

        $actions->name('edit')
                ->label('Create')
                ->bind(Form::create()
                        ->section('Details', [
                                Field::name('entity')->label('Entity')->entityFrom($this->repository),
                                Field::name('data')->label('Data')->string()->required()->maxLength(500)
                        ])
                )
                ->to(function (array $input) {
                    $entity       = $input['entity'];
                    $entity->data = $input['data'];

                    $this->repository->save($entity);
                })
                ->authorize(self::PERMISSION_EDIT);

        $actions->name('delete')
                ->label('Delete')
                ->bind('Delete')
                ->bind(Form::create()
                        ->section('Details', [
                                Field::name('entity')->label('Entity')->entityFrom($this->repository),
                        ])
                )
                ->to(function (array $input) {
                    $this->repository->remove($input['entity']);
                });
    }

    protected function table(IForm $form)
    {
        return Table::create([
                $form->getField('data'),
                ActionButtonColumn::create([
                        $this->action('edit'),
                        $this->action('delete'),
                ])
        ]);
    }
}