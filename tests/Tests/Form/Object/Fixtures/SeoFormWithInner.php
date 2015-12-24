<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Field\Builder\Field as Field;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SeoFormWithInner extends IndependentFormObject
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
     * @var KeywordForm[]
     */
    public $keywords;

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    protected function defineForm(FormObjectDefinition $form)
    {
        $form->section('SEO Details', [
            //
            $form->field($this->title)->name('title')->label('Title')->string()->maxLength(50)->required(),
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
                    ->arrayOf(Field::element()->form(new KeywordForm())->required())
                    ->required(),
        ]);
    }
}