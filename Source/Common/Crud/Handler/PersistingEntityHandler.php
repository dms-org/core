<?php

namespace Iddigital\Cms\Core\Common\Crud\Handler;

use Iddigital\Cms\Core\Module\Handler\ParameterizedActionHandler;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The persisting entity handler class base
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class PersistingEntityHandler extends ParameterizedActionHandler
{
    /**
     * @var IRepository
     */
    protected $repository;

    public function __construct(IRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }
}