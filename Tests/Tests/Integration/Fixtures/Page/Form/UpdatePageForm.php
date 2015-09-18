<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form;

use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpdatePageForm extends PageForm
{
    /**
     * @var PageDetailsForm
     */
    public $details;

    /**
     * {@inheritDoc}
     */
    protected function defineForm(FormObjectDefinition $form, Page $page)
    {
        parent::defineForm($form, $page);

        $form->bind($this->details)->to(new PageDetailsForm($page));
    }
}