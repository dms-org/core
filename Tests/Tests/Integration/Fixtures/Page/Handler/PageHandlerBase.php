<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Handler;

use Iddigital\Cms\Core\Module\Handler\ParameterizedActionHandler;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Persistance\IPageRepository;
use Iddigital\Cms\Core\Util\IClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PageHandlerBase extends ParameterizedActionHandler
{
    /**
     * @var IClock
     */
    protected $clock;

    /**
     * @var IPageRepository
     */
    protected $pageRepository;

    /**
     * CreatePageHandler constructor.
     *
     * @param IClock          $clock
     * @param IPageRepository $pageRepository
     */
    public function __construct(IClock $clock, IPageRepository $pageRepository)
    {
        parent::__construct();

        $this->clock          = $clock;
        $this->pageRepository = $pageRepository;
    }
}