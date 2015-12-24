Form Objects
============

Form objects allow you to reuse a particular form by encapsulating its fields as a class.
Additionally the class acts as a strongly-typed DTO for the submitted form as an extra benefit.

Simple form objects can be defined using the `Dms\Core\Form\Object\IndependentFormObject` base class.

```php
<?php

namespace Some\Name\Space;

use Dms\Core\Form\Object\IndependentFormObject;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\InvalidFormSubmissionException;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageForm extends IndependentFormObject
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $subTitle;

    /**
     * @var string
     */
    public $content;

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     */
    protected function defineForm(FormObjectDefinition $form)
    {
        $form->section('Page Content', [
            //
            $form->field($this->title)->name('title')->label('Title')->string()->required()->maxLength(50),
            //
            $form->field($this->subTitle)->name('sub_title')->label('Sub Title')->string()->maxLength(50),
            //
            $form->field($this->content)->name('content')->label('Content')->string()->html()->required(),
        ]);
    }
}

$form = new PageForm();
// You can load the form object using the 'submit' method.
$form->submit([
    'title'     => 'Title',
    'sub_title' => 'Sub Title',
    'content'   => 'content!'
]);

$form->title; // 'Title'
$form->subTitle; // 'Sub Title'
$form->conent; // 'content!'

try {
    $form->submit([
        'title'     => null,
        'sub_title' => 'Sub Title',
        'content'   => 'content!'
    ]);
} catch (InvalidFormSubmissionException $e) {
    // If the input fails validation this exception will be thrown
}
```

Staged Form Objects
===================

The equivalent class exists form staged forms. They are slightly more difficult to implement
and the property type data cannot be inferred ahead of type and hence must be defined separately.

A basic staged form object can be defined as follows:


```php
<?php

namespace Some\Name\Space;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Form\Field\Builder\Field;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfNumbersStagedForm extends StagedFormObject
{
    /**
     * @var int
     */
    public $length;

    /**
     * @var int[]
     */
    public $numbers;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->length)->asInt();
        $class->property($this->numbers)->asArrayOf(Type::int());
    }

    /**
     * Defines the staged form.
     *
     * @param StagedFormObjectDefinition $form
     */
    protected function defineForm(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $form) {
            $form->section('Length', [
                    $form->field($this->length)
                            ->name('length')
                            ->label('Length')
                            ->int()
                            ->required()
                            ->greaterThan(0)
            ]);
        });

        $form->stageDependentOn(['length'], function (FormObjectDefinition $form) {
            $form->section('Numbers', [
                    $form->field($this->numbers)
                            ->name('numbers')
                            ->label('Numbers')
                            ->arrayOf(Field::element()->int()->required())
                            ->required()
                            ->exactLength($this->length)
            ]);
        });
    }
}

$stagedForm = new ArrayOfNumbersStagedForm();

$stagedForm->getFirstForm()->getFieldNames(); // ['length']
$stagedForm->getFormForStage(2, [
    'length' => '3'
])->getFieldNames(); // ['numbers']

// You can load the staged form object using the 'submit' method.
$stagedForm->submit([
    'length'  => '3',
    'numbers' => ['1', '2', '3'],
]);

$stagedForm->length; // 3
$stagedForm->numbers; // [1, 2, 3]

try {
    $stagedForm->submit([
        'length'  => '2',
        'numbers' => ['1'],
    ]);
} catch (InvalidFormSubmissionException $e) {
    // If the input fails validation this exception will be thrown
}
```