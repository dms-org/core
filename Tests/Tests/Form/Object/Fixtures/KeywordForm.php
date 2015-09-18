<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Fixtures;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\IndependentFormObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class KeywordForm extends IndependentFormObject
{
    /**
     * @var string
     */
    public $keyword;

    /**
     * KeywordForm constructor.
     *
     * @param string $keyword
     */
    public function __construct($keyword = '')
    {
        parent::__construct();
        $this->keyword = $keyword;
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
        $form->section('Keyword', [
            $form->field($this->keyword)->name('keyword')->label('Keyword')->string()->required(),
        ]);
    }
}