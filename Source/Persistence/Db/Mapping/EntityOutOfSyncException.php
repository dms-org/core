<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Persistence\Db\Connection\DbOutOfSyncException;

/**
 * The entity out of sync exception.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityOutOfSyncException extends BaseException
{
    /**
     * @var IEntity
     */
    private $entityBeingPersisted;

    /**
     * @var IEntity|null
     */
    private $currentEntityInDb;

    /**
     * EntityOutOfSyncException constructor.
     *
     * @param IEntity              $entityBeingPersisted
     * @param IEntity|null         $currentEntityInDb
     * @param DbOutOfSyncException $innerException
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IEntity $entityBeingPersisted, IEntity $currentEntityInDb = null, DbOutOfSyncException $innerException)
    {
        parent::__construct(
                sprintf(
                        'Could not persist entity %s with id %d: the database is out of sync',
                        get_class($entityBeingPersisted), $entityBeingPersisted->getId()
                ),
                null,
                $innerException
        );

        if ($currentEntityInDb && $entityBeingPersisted->getId() !== $currentEntityInDb->getId()) {
            throw InvalidArgumentException::format(
                    'Invalid call to %s: entity ids must be the same, %d and %d given',
                    __METHOD__, $entityBeingPersisted->getId(), $currentEntityInDb->getId()
            );
        }

        $this->entityBeingPersisted = $entityBeingPersisted;
        $this->currentEntityInDb    = $currentEntityInDb;
    }

    /**
     * @return IEntity
     */
    public function getEntityBeingPersisted()
    {
        return $this->entityBeingPersisted;
    }

    /**
     * @return bool
     */
    public function hasCurrentEntityInDb()
    {
        return $this->currentEntityInDb !== null;
    }

    /**
     * @return IEntity|null
     */
    public function getCurrentEntityInDb()
    {
        return $this->currentEntityInDb;
    }
}