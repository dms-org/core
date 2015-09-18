<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler;

use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\DeletePageForm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DeletePageHandler extends PageHandlerBase
{
    public function handle(DeletePageForm $form)
    {
        $this->pageRepository->remove($form->page);
    }
}