<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler;

use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\CreatePageForm;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Result\PageCreatedResult;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreatePageHandler extends PageHandlerBase
{
    public function handle(CreatePageForm $form)
    {
        $page = Page::createNew(
                $form->title,
                $form->content,
                $form->seo->asValueObject(),
                $this->clock
        );

        $this->pageRepository->save($page);

        return new PageCreatedResult($page->getId());
    }
}