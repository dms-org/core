Forms
=====

Form represent an api for processing and binding user input from the web in a
specific format. Forms consist of a list of sections which in-turn consist of
a list of fields.

A simple form can be defined using the fluent builder objects:

```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;

$form = Form::create()
        ->section('Name', [
                Field::name('first_name')->label('First Name')->string()->required()->maxLength(50),
                Field::name('middle_name')->label('Middle Name')->string()->maxLength(50),
                Field::name('last_name')->label('Last Name')->string()->required()->maxLength(50),
        ])
        ->section('Details', [
                Field::name('age')->label('Age')->int()->required()->min(0)->max(130),
                Field::name('married')->label('Married?')->bool(),
        ])
        ->build();

$processedData = $form->process([
    'first_name' => 'Jason',
    'last_name'  => 'Burke',
    'age'        => '28',
    'married'    => '1',
]);

/**
 * $processedData:
 * [
 *      'first_name'  => 'Jason',
 *      'middle_name' => null',
 *      'last_name'   => 'Burke',
 *      'age'         => 28,
 *      'married'     => true,
 * ]
 */

try {
    $form->process([
        'first_name' => 'Jason',
        'last_name'  => 'Burke',
        'age'        => 'abc',
        'married'    => '0',
    ]);
} catch (InvalidFormSubmissionException $e) {
    // If the input fails validation this exception will be thrown
}
```