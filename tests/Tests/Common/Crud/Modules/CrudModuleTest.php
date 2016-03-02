<?php

namespace Dms\Core\Tests\Common\Crud\Modules;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Persistence\IRepository;
use Dms\Core\Tests\Module\Mock\MockAuthSystem;

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
     * @return IIdentifiableObjectSet
     */
    final protected function buildDataSource() : IIdentifiableObjectSet
    {
        return $this->buildRepositoryDataSource();
    }

    /**
     * @return IMutableObjectSet
     */
    abstract protected function buildRepositoryDataSource() : IMutableObjectSet;

    /**
     * @param IIdentifiableObjectSet $dataSource
     * @param MockAuthSystem         $authSystem
     *
     * @return IReadModule
     */
    final protected function buildReadModule(IIdentifiableObjectSet $dataSource, MockAuthSystem $authSystem) : IReadModule
    {
        /** @var IMutableObjectSet $dataSource */
        return $this->buildCrudModule($dataSource, $authSystem);
    }

    /**
     * @param IMutableObjectSet    $dataSource
     * @param MockAuthSystem $authSystem
     *
     * @return ICrudModule
     */
    abstract protected function buildCrudModule(IMutableObjectSet $dataSource, MockAuthSystem $authSystem) : ICrudModule;
}