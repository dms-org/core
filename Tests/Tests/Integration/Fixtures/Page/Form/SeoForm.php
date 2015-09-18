<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form;

use Iddigital\Cms\Core\Form\Field\Builder\Field as Field;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\IndependentFormObject;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Seo;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SeoForm extends IndependentFormObject
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
     * @var string[]
     */
    public $keywords;

    public function __construct(Seo $seo = null)
    {
        parent::__construct();

        if ($seo) {
            $this->title       = $seo->title;
            $this->description = $seo->description;
            $this->keywords    = $seo->keywords;
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
        $form->section('SEO Details', [
            //
            $form->field($this->title)->name('title')->label('Title')->string()->maxLength(50)->required(),
            //
            $form->field($this->description)->name('description')->label('Description')->string()->maxLength(255)->required(),
            //
            $form->field($this->keywords)->name('keywords')->label('Keywords')->arrayOf(Field::element()->string()->required()),
        ]);
    }

    /**
     * @return Seo
     */
    public function asValueObject()
    {
        return Seo::build($this->title, $this->description, $this->keywords);
    }
}