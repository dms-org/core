Staged Forms
============

It is often the case that a static web form is not enough, some fields may have to be dynamic,
based on previously entered data.

For these semi-common cases, a broader abstraction is required. To tackle this, the core cms
project introduces the concept of *staged forms* which can be described as multiple forms chained
together, each form acting as a *stage* where subsequent stages can depend on the input on
previous stages, all together combining at the end as one single form.

Building on top of the basic forms, staged forms can be defined as follows:


```php
<?php

namespace Some\Name\Space;

use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\InvalidFormSubmissionException;

$stagedForm = StagedForm::begin(
        Form::create()
                ->section('User Details', [
                        Field::name('age')->label('Age')->int()->required()->min(0)
                ])
)->then(function (array $data) {
    if ($data['age'] >= 50) {
        return Form::create()
                ->section('Details', [
                        Field::name('wear_glasses')->label('Wear Glasses?')->bool()
                ]);
    } else {
        return Form::create()
                ->section('Details', [
                        Field::name('play_sports')->label('Play Sports?')->bool()
                ]);
    }
})->build();

$stagedForm->getFirstForm()->getFieldNames(); // ['age']

$stagedForm->getFormForStage(2, ['age' => '20'])->getFieldNames(); // ['play_sports']
$stagedForm->getFormForStage(2, ['age' => '70'])->getFieldNames(); // ['wear_glasses']

$processedData = $stagedForm->process([
    'age'         => '20',
    'play_sports' => '1',
]);

/**
 * $processedData:
 * [
 *      'age'         => 20,
 *      'play_sports' => true,
 * ]
 */

try {
    $processedData = $stagedForm->process([
        'age'         => '70',
        'play_sports' => '1', // Should be 'wear_glasses'
    ]);
} catch (InvalidFormSubmissionException $e) {
    // If the input fails validation this exception will be thrown
}
```