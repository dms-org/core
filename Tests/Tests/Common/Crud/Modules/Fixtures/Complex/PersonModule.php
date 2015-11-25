<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex;

use Iddigital\Cms\Core\Common\Crud\CrudModule;
use Iddigital\Cms\Core\Common\Crud\Definition\CrudModuleDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Form\CrudFormDefinition;
use Iddigital\Cms\Core\Common\Crud\Definition\Table\SummaryTableDefinition;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Table\Builder\Column;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Adult;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Child;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Colour;
use Iddigital\Cms\Core\Tests\Common\Crud\Modules\Fixtures\Complex\Domain\Person;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PersonModule extends CrudModule
{

    /**
     * Defines the structure of this module.
     *
     * @param CrudModuleDefinition $module
     */
    protected function defineCrudModule(CrudModuleDefinition $module)
    {
        $module->name('people-module');

        $module->labelObjects()->fromCallback(function (Person $person) {
            return $person->getFullName();
        });

        $module->crudForm(function (CrudFormDefinition $form) {
            $form->section('Person Details', [
                    $form->field(Field::name('first_name')->label('First Name')->string()->required())
                            ->bindToProperty(Person::FIRST_NAME),
                    $form->field(Field::name('last_name')->label('Last Name')->string()->required())
                            ->bindToProperty(Person::LAST_NAME),
                    $form->field(Field::name('age')->label('Age')->int()->required())
                            ->bindToProperty(Person::AGE),
            ]);

            $form->dependentOn(['age'], function (CrudFormDefinition $form, array $input, Person $object = null) {
                if ($input['age'] < Person::COMING_OF_AGE) {
                    $form->mapToSubClass(Child::class);
                    $form->section('Kid Details', [
                            $form->field(
                                    Field::name('favourite_colour')
                                            ->label('Favourite Colour')
                                            ->enum(Colour::class, [
                                                    Colour::RED    => 'Red',
                                                    Colour::GREEN  => 'Green',
                                                    Colour::BLUE   => 'Blue',
                                                    Colour::YELLOW => 'Yellow',
                                            ])
                                            ->required()
                            )->bindToProperty(Child::FAVOURITE_COLOUR),
                    ]);
                } else {
                    $form->mapToSubClass(Adult::class);
                    $form->section('Adult Details', [
                            $form->field(Field::name('profession')->label('Profession')->string()->required())
                                    ->bindToProperty(Adult::PROFESSION),
                    ]);
                }
            });

            // TODO: remove duplication
            $form->createObjectType()->fromCallback(function (array $input) {
                if ($input['age'] < Person::COMING_OF_AGE) {
                    return Child::class;
                } else {
                    return Adult::class;
                }
            });
        });


        $module->removeAction()->deleteFromRepository();

        $module->summaryTable(function (SummaryTableDefinition $table) {
            $table->mapCallback(function (Person $person) {
                return $person instanceof Adult ? 'adult' : 'child';
            })->to(Field::name('type')->label('Type')->string()->oneOf(['child' => 'Child', 'adult' => 'Adult']));

            $table->column(Column::name('name')->label('Name')->components([
                    Field::name('first')->label('First Name')->string(),
                    Field::name('last')->label('Last Name')->string(),
            ]));

            $table->mapProperty(Person::FIRST_NAME)->toComponent('name.first');
            $table->mapProperty(Person::LAST_NAME)->toComponent('name.last');
            $table->mapProperty(Person::AGE)->to(Field::name('age')->label('Age')->int());

            $table->view('default', 'Default')
                    ->loadAll()
                    ->asDefault();

            $table->view('grouped-by-type', 'In Age Groups')
                    ->loadAll()
                    ->groupBy('type');
        });
    }
}