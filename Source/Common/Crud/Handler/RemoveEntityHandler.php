<?php

namespace Iddigital\Cms\Core\Common\Crud\Handler;

use Iddigital\Cms\Core\Common\Crud\Form\RemoveEntityDto;
use Iddigital\Cms\Core\Common\Crud\Result\EntityUpdatedResult;
use Iddigital\Cms\Core\Persistence\IRepository;

/**
 * The remove entity handler class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RemoveEntityHandler extends PersistingEntityHandler
{
    public function __construct(IRepository $repository)
    {
        parent::__construct($repository);
    }

    public function handle(RemoveEntityDto $removeEntityDto)
    {
        $entity = $removeEntityDto->entity;

        $this->repository->remove($entity);

        return new EntityUpdatedResult($entity);
    }
}