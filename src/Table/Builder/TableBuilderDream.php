<?php declare(strict_types = 1);

namespace Dms\Core\Table\Builder;

use Dms\Core\Form\Field\Builder\Field;

Table::create([
    Column::from(Field::name('data')->label('Data')->string()),
    Column::name('name')->label('Name')->components([
            Field::name('first_name')->label('First Name')->string()->required(),
            Field::name('last_name')->label('Last Name')->string()->required(),
    ]),
    ActionButtonListColumn::create()
]);