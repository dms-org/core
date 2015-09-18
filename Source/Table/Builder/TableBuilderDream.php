<?php

namespace Iddigital\Cms\Core\Table\Builder;

use Iddigital\Cms\Core\Form\Field\Builder\Field;

Table::create([
    Column::from(Field::name('data')->label('Data')->string()),
    Column::name('name')->label('Name')->components([
            Field::name('first_name')->label('First Name')->string()->required(),
            Field::name('last_name')->label('Last Name')->string()->required(),
    ]),
    ActionButtonListColumn::create()
]);