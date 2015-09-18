<?php

namespace Iddigital\Cms\Core\Common\Crud\Handler;

use Iddigital\Cms\Core\Common\Crud\Form\ICreateEntityFormObject;
use Iddigital\Cms\Core\Common\Crud\Result\EntityCreatedResult;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The create entity handler class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CreateEntityHandler extends PersistingEntityHandler
{
    public function __construct(IRepository $repository)
    {
        parent::__construct($repository);
    }


    public function handle(ICreateEntityFormObject $formObject)
    {
        $entity = $formObject->populateNewEntity();

        $this->repository->save($entity);

        return new EntityCreatedResult($entity);
    }
}