<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Persistance;

use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Persistence\ArrayRepository;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageRepository extends ArrayRepository implements IPageRepository
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct(new EntityCollection(Page::class));
    }

}