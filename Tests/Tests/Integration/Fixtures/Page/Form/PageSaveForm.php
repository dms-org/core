<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form;

use Iddigital\Cms\Core\Common\Crud\Form\PersistEntityFormObject;
use Iddigital\Cms\Core\Form\Object\DependentFormObject;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageSaveForm extends PersistEntityFormObject
{
    /**
     * @var Page
     */
    public $page;

    /**
     * PageForm constructor.
     *
     * @param Page $page
     */
    public function __construct(Page $page)
    {
        parent::__construct(function ($form) use ($page) {
            $this->defineForm($form, $page);
        });
    }

    /**
     * @inheritDoc
     */
    protected function defineFormObject(FormObjectDefinition $form, Page $page)
    {
        $form->getClass()->property($this->page)->asObject(Page::class);

        $this->page = $page;
    }
}