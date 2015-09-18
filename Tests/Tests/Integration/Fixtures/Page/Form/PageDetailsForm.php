<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\IndependentFormObject;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageDetailsForm extends IndependentFormObject
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var SeoForm
     */
    public $seo;

    public function __construct(Page $page = null)
    {
        parent::__construct();

        if($page) {
            $this->title   = $page->title;
            $this->content = $page->content;
            $this->seo     = new SeoForm($page->seo);
        }
    }

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
            $form->field($this->content)->name('content')->label('Content')->string()->required(),
        ]);

        $form->bind($this->seo)->to(new SeoForm());
    }
}