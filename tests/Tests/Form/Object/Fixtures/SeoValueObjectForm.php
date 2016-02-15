<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\ValueObjectFormObject;
use Dms\Core\Model\IValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SeoValueObjectForm extends ValueObjectFormObject
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $keywords;

    /**
     * Gets the type of value object of this form.
     *
     * @return string
     */
    protected function valueObjectType() : string
    {
        return SeoValueObject::class;
    }

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     */
    protected function defineFormObject(FormObjectDefinition $form)
    {
        $form->section('SEO', [
            //
            $form->field($this->title)
                    ->name('title')
                    ->label('Title')
                    ->string()
                    ->maxLength(50)
                    ->required(),
            //
            $form->field($this->description)
                    ->name('description')
                    ->label('Description')
                    ->string()
                    ->maxLength(255)
                    ->required(),
            //
            $form->field($this->keywords)
                    ->name('keywords')
                    ->label('Keywords')
                    ->arrayOf(Field::element()->string()->required())
                    ->required(),
        ]);
    }

    /**
     * Populates the form with the value object's values.
     *
     * @param IValueObject $valueObject
     *
     * @return void
     */
    protected function populateFormWithValueObject(IValueObject $valueObject)
    {
        /** @var SeoValueObject $valueObject */
        $this->title       = $valueObject->title;
        $this->description = $valueObject->description;
        $this->keywords    = $valueObject->keywords;
    }

    /**
     * Creates a value object with the form's values.
     *
     * @return IValueObject
     */
    protected function populateValueObjectFromForm() : IValueObject
    {
        return new SeoValueObject($this->title, $this->description, $this->keywords);
    }
}