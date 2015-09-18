<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page;

use Iddigital\Cms\Core\Module\Module;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\CreatePageForm;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\DeletePageForm;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Form\UpdatePageForm;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler\ClearPagesHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler\CreatePageHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler\DeletePageHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler\UpdatePageHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Persistance\IPageRepository;
use Iddigital\Cms\Core\Util\IClock;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageModule extends Module
{
    const PERMISSION_VIEW = 'page.view';
    const PERMISSION_CREATE = 'page.create';
    const PERMISSION_UPDATE = 'page.update';
    const PERMISSION_DELETE = 'page.delete';

    /**
     * @var IClock
     */
    private $clock;

    /**
     * @var IPageRepository
     */
    private $repository;

    /**
     * @inheritDoc
     */
    public function __construct($name, array $actions, IClock $clock, IPageRepository $repository)
    {
        parent::__construct($this->name(), $actions, $repository);
        $this->clock = $clock;
    }

    protected function name()
    {
        return 'Pages';
    }

    public function overviewQuery()
    {
        return $this->query();
    }

    public function createAction()
    {
        return $this->bind(new CreatePageForm())
                ->to(new CreatePageHandler($this->clock, $this->repository))
                ->authorize(self::PERMISSION_CREATE);
    }

    public function updateAction(Page $page)
    {
        return $this->bind(new UpdatePageForm($page))
                ->to(new UpdatePageHandler($this->clock, $this->repository))
                ->authorize(self::PERMISSION_UPDATE);
    }

    public function deleteAction(Page $page)
    {
        return $this->bind(new DeletePageForm($page))
                ->to(new DeletePageHandler($this->clock, $this->repository))
                ->authorize(self::PERMISSION_DELETE);
    }

    public function clearAction()
    {
        return $this->action(ClearPagesHandler::class)
                ->authorize(self::PERMISSION_DELETE);
    }
}