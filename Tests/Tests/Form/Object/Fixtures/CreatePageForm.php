<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Object\IndependentFormObject;
use Dms\Core\Form\Object\FormObjectDefinition;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatePageForm extends IndependentFormObject
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
     * @var SeoForm
     */
    public $seo;

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     *
     * @return void
     */
    protected function defineForm(FormObjectDefinition $form)
    {
        $form->section('Page Content', [
            //
            $form->field($this->title)->name('title')->label('Title')->string()->required()->maxLength(50),
            //
            $form->field($this->subTitle)->name('sub_title')->label('Sub Title')->string()->maxLength(50),
            //
            $form->field($this->content)->name('content')->label('Content')->string()->required(),
        ]);

        $form->bind($this->seo)->to(new SeoForm());
    }
}