<?php declare(strict_types = 1);

namespace Dms\Core\Model\Subset;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Criteria;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\Type\IType;

/**
 * The object subset class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectSetSubset implements IObjectSet, IObjectSetWithLoadCriteriaSupport
{
    /**
     * @var IObjectSet
     */
    protected $fullObjectSet;

    /**
     * @var ICriteria
     */
    protected $criteria;

    /**
     * ObjectSetSubset constructor.
     *
     * @param IObjectSet $fullObjectSet
     * @param ICriteria  $criteria
     */
    public function __construct(IObjectSet $fullObjectSet, ICriteria $criteria)
    {
        $criteria->verifyOfClass($fullObjectSet->getObjectType());
        $this->fullObjectSet = $fullObjectSet;
        $this->criteria      = $criteria;
    }

    /**
     * @return IObjectSet
     */
    public function getFullObjectSet() : IObjectSet
    {
        return $this->fullObjectSet;
    }

    /**
     * @return ICriteria
     */
    public function getCriteria() : ICriteria
    {
        return $this->criteria;
    }

    /**
     * @inheritdoc
     */
    public function getObjectType() : string
    {
        return $this->criteria->getClass()->getClassName();
    }

    /**
     * @inheritdoc
     */
    public function getElementType() : IType
    {
        return $this->fullObjectSet->getElementType();
    }

    /**
     * @inheritdoc
     */
    public function criteria() : Criteria
    {
        return $this->fullObjectSet->criteria()->whereInstanceOf($this->getObjectType());
    }

    /**
     * @inheritdoc
     */
    public function getAll() : array
    {
        return $this->fullObjectSet->matching($this->criteria);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->fullObjectSet->countMatching($this->criteria);
    }

    /**
     * @inheritdoc
     */
    public function contains($object)
    {
        return $this->containsAll([$object]);
    }

    /**
     * @inheritdoc
     */
    public function containsAll(array $objects) : bool
    {
        return $this->fullObjectSet->countMatching(
            $this->criteria->asMutableCriteria()
                ->whereIn('this', $objects)
        ) === count($objects);
    }

    /**
     * @inheritdoc
     */
    public function countMatching(ICriteria $criteria) : int
    {
        return $this->fullObjectSet->countMatching($this->criteria->merge($criteria));
    }

    /**
     * @inheritdoc
     */
    public function matching(ICriteria $criteria) : array
    {
        return $this->fullObjectSet->matching(
            $this->criteria->merge($criteria)
        );
    }

    /**
     * @inheritdoc
     */
    public function satisfying(ISpecification $specification) : array
    {
        return $this->matching($specification->asCriteria());
    }

    /**
     * @inheritdoc
     */
    public function subset(ICriteria $criteria) : IObjectSet
    {
        return $this->fullObjectSet->subset(
            $criteria->merge($criteria)
        );
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getAll());
    }

    /**
     * @inheritdoc
     */
    public function loadCriteria() : LoadCriteria
    {
        $objectSet = $this->verifyObjectSetSupportsLoadCriteria(__METHOD__);

        return $objectSet->loadCriteria()->whereInstanceOf($this->getObjectType());
    }

    /**
     * @inheritdoc
     */
    public function loadMatching(ILoadCriteria $criteria) : array
    {
        $objectSet = $this->verifyObjectSetSupportsLoadCriteria(__METHOD__);

        return $objectSet->loadMatching(
            $criteria->merge($this->criteria)
        );
    }

    private function verifyObjectSetSupportsLoadCriteria(string $method) : IObjectSetWithLoadCriteriaSupport
    {
        if (!($this->fullObjectSet instanceof IObjectSetWithLoadCriteriaSupport)) {
            throw Exception\InvalidOperationException::format(
                'Invalid call to %s: object set of type %s<%s> does not support load criteria',
                $method, get_class($this->fullObjectSet), $this->getObjectType()
            );
        }

        return $this->fullObjectSet;
    }
}