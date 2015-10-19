<?php

namespace Iddigital\Cms\Core\Common\Crud\Definition;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Model\ITypedCollection;
use Iddigital\Cms\Core\Module\Definition\ModuleDefinition;

/**
 * The read module definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModuleDefinition extends ModuleDefinition
{
    /**
     * @var ITypedCollection
     */
    protected $dataSource;

    /**
     * @var ITypedCollection
     */
    protected $summaryTableDataSource;

    /**
     * ReadModuleDefinition constructor.
     *
     * @param IAuthSystem      $authSystem
     * @param ITypedCollection $dataSource
     */
    public function __construct(IAuthSystem $authSystem, ITypedCollection $dataSource)
    {
        parent::__construct($authSystem);
        $this->dataSource = $dataSource;
    }


}