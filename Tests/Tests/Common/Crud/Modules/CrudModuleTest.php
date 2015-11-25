<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Modules;

use Iddigital\Cms\Core\Common\Crud\ICrudModule;
use Iddigital\Cms\Core\Common\Crud\IReadModule;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Persistence\IRepository;
use Iddigital\Cms\Core\Tests\Module\Mock\MockAuthSystem;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class CrudModuleTest extends ReadModuleTest
{
    /**
     * @var IRepository
     */
    protected $dataSource;

    /**
     * @var ICrudModule
     */
    protected $module;

    /**
     * @return IEntitySet
     */
    final protected function buildDataSource()
    {
        return $this->buildRepositoryDataSource();
    }

    /**
     * @return IRepository
     */
    abstract protected function buildRepositoryDataSource();

    /**
     * @param IEntitySet     $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return IReadModule
     */
    final protected function buildReadModule(IEntitySet $dataSource, MockAuthSystem $authSystem)
    {
        /** @var IRepository $dataSource */
        return $this->buildCrudModule($dataSource, $authSystem);
    }

    /**
     * @param IRepository    $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return ICrudModule
     */
    abstract protected function buildCrudModule(IRepository $dataSource, MockAuthSystem $authSystem);
}