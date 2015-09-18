<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler;

use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\UpdatePageForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpdatePageHandler extends PageHandlerBase
{
    public function handle(UpdatePageForm $form)
    {
        $page = $form->page;

        $page->title     = $form->details->title;
        $page->content   = $form->details->content;
        $page->seo       = $form->details->seo->asValueObject();
        $page->updatedAt = $this->clock->now();

        $this->pageRepository->save($page);
    }
}