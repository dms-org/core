<?php

namespace Iddigital\Cms\Core\Common\Crud;

use Iddigital\Cms\Core\Module\IParameterizedAction;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The interface for a CRUD module.
 *
 * This provides a set of CRUD actions and displays regarding
 * a repository.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ICrudModule extends IReadModule
{
    /**
     * Gets the type of entities contained within the repository.
     *
     * @return string
     */
    public function getEntityType();

    /**
     * Gets the underlying repository instance.
     *
     * @return IRepository
     */
    public function getRepository();

    /**
     * Gets the create entity action.
     *
     * @return IParameterizedAction
     */
    public function getCreateAction();

    /**
     * Gets the edit entity action.
     *
     * @return IParameterizedAction
     */
    public function getEditAction();

    /**
     * Gets remove entity action.
     *
     * @return IParameterizedAction
     */
    public function getRemoveAction();
}