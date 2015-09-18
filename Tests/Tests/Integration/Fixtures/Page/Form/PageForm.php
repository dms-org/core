<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form;

use Iddigital\Cms\Core\Form\Object\DependentFormObject;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageForm extends DependentFormObject
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

    protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->page)->asObject(Page::class);
    }

    /**
     * @inheritDoc
     */
    protected function defineForm(FormObjectDefinition $form, Page $page)
    {
        $this->page = $page;
    }
}