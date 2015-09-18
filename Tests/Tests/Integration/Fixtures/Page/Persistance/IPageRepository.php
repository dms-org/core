<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Persistance;

use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain\Page;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IPageRepository extends IRepository
{
    /**
     * {@inheritDoc}
     * @return Page[]
     */
    public function getAll();

    /**
     * {@inheritDoc}
     * @return Page
     */
    public function get($id);

    /**
     * {@inheritDoc}
     * @return Page|null
     */
    public function tryGet($id);

    /**
     * {@inheritDoc}
     * @return Page[]
     */
    public function tryGetAll(array $ids);
}