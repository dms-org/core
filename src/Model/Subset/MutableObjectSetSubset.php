<?php declare(strict_types = 1);

namespace Dms\Core\Model\Subset;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\Entity;

/**
 * The mutable object subset class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MutableObjectSetSubset extends IdentifiableObjectSetSubset implements IMutableObjectSet, IObjectSetWithLoadCriteriaSupport
{
    /**
     * @var IMutableObjectSet
     */
    protected $fullObjectSet;

    /**
     * @inheritDoc
     */
    public function __construct(IMutableObjectSet $fullObjectSet, ICriteria $criteria)
    {
        parent::__construct($fullObjectSet, $criteria);
    }

    /**
     * @inheritDoc
     */
    public function save(ITypedObject $object)
    {
        $this->saveAll([$object]);
    }

    /**
     * @inheritDoc
     */
    public function saveAll(array $objects)
    {
        $this->fullObjectSet->saveAll($objects);
    }

    /**
     * @inheritDoc
     */
    public function remove($object)
    {
        $this->removeAll([$object]);
    }

    /**
     * @inheritDoc
     */
    public function removeById($id)
    {
        $this->removeAllById([$id]);
    }

    /**
     * @inheritDoc
     */
    public function removeAll(array $objects)
    {
        $ids = [];

        foreach ($objects as $object) {
            $ids[] = $this->getObjectId($object);
        }

        $this->removeAllById($ids);
    }

    /**
     * @inheritDoc
     */
    public function removeAllById(array $ids)
    {
        if ($this->fullObjectSet instanceof IEntitySet) {
            $this->removeMatching(
                $this->criteria->asMutableCriteria()
                    ->whereIn(Entity::ID, $ids)
            );
        } else {
            $this->fullObjectSet->removeAll($this->tryGetAll($ids));
        }
    }

    /**
     * @inheritDoc
     */
    public function removeMatching(ICriteria $criteria)
    {
        $this->fullObjectSet->removeMatching(
            $this->criteria->merge($criteria)
        );
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->fullObjectSet->removeMatching($this->criteria);
    }
}