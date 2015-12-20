<?php

namespace Dms\Core\Common\Crud\Dream\Simple;

use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IForm;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Table\Builder\Table;

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
        return new SomeEntityRepository($connection);
    }

    protected function actions(ActionListDefinition $actions)
    {
        $actions->name('create')
                ->label('Create')
                ->bind(Form::create()
                        ->section('Details', [
                                Field::name('data')->label('Data')->string()->required()->maxLength(500)
                        ])
                )
                ->to(function (array $input) {
                    $this->repository->save(new SomeEntity(null, $input['data']));
                })
                ->authorize(self::PERMISSION_CREATE);

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