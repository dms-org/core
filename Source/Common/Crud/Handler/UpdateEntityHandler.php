<?php

namespace Iddigital\Cms\Core\Common\Crud\Handler;

use Iddigital\Cms\Core\Common\Crud\Form\IUpdateEntityFormObject;
use Iddigital\Cms\Core\Common\Crud\Result\EntityUpdatedResult;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The update entity handler class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UpdateEntityHandler extends PersistingEntityHandler
{
    public function __construct(IRepository $repository)
    {
        parent::__construct($repository);
    }

    public function handle(IUpdateEntityFormObject $formObject)
    {
        $entity = $formObject->getEntity();
        $formObject->populateEntity($entity);

        $this->repository->save($entity);

        return new EntityUpdatedResult($entity);
    }
}