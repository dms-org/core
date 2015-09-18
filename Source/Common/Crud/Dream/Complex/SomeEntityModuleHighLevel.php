<?php

namespace Iddigital\Cms\Core\Common\Crud\Dream\Complex;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IForm;
use Iddigital\Cms\Core\Persistence\Db\Connection\IConnection;
use Iddigital\Cms\Core\Table\Builder\Table;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SomeEntityModuleHighLevel extends CrudModule
{
    protected function define(ModuleDefinition $module)
    {
        $module->name('some-entity');
        $module->label('Some Entity', 'Some Entities');
    }

    protected function loadRepository(IConnection $connection)
    {
        return new ProductRepository($connection);
    }

    protected function actions(ActionListDefinition $actions)
    {
        $form = $this->form();

        $actions->create($form)
                ->handle(function (array $input) {
                    return new Product(null, $input['data']);
                });

        $actions->edit($form)
                ->handle(function (Product $entity, array $input) {
                    $entity->data = $input['data'];
                });

        $actions->delete();
    }

    protected function table()
    {
        $form = $this->form();

        return Table::create([
                $form->getField('data'),
                $this->actionButtons()
        ]);
    }

    protected function form()
    {
        return Form::create()
                ->section('Details', [
                        Field::name('data')->label('Data')->string()->required()->maxLength(500)
                ]);
    }
}