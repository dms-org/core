<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler;

use Iddigital\Cms\Core\Module\Handler\UnparameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Persistance\IPageRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ClearPagesHandler extends UnparameterizedActionHandler
{
    /**
     * @var IPageRepository
     */
    protected $pageRepository;

    /**
     * ClearPagesHandler constructor.
     *
     * @param IPageRepository $pageRepository
     */
    public function __construct(IPageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function handle()
    {
        $this->pageRepository->clear();
    }
}