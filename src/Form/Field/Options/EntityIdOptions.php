<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Exception\InvalidOperationException;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntity;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\ILoadCriteria;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\ObjectType;

/**
 * The entity options class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdOptions extends ObjectIdentityOptions
{
    /**
     * @var IEntitySet
     */
    protected $objects;

    /**
     * @var string[]
     */
    private $filterByMemberExpressions;

    /**
     * EntityIdOptions constructor.
     *
     * @param IEntitySet    $entities
     * @param callable|null $labelCallback
     * @param string|null   $labelMemberExpression
     * @param callable|null $enabledCallback
     * @param callable|null $disabledLabelCallback
     * @param string[]      $filterByMemberExpressions
     */
    public function __construct(
            IEntitySet $entities,
            callable $labelCallback = null,
            string $labelMemberExpression = null,
            callable $enabledCallback = null,
            callable $disabledLabelCallback = null,
            array $filterByMemberExpressions = []
    ) {
        parent::__construct($entities, $labelCallback, $labelMemberExpression, $enabledCallback, $disabledLabelCallback);
        $this->filterByMemberExpressions = $filterByMemberExpressions;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll() : array
    {
        if ($this->canLoadViaOptimizedCriteria()) {
            return $this->loadOptionsViaOptimizedLoadCriteria($this->objects, $this->labelMemberExpression);
        }

        return parent::getAll();
    }

    /**
     * @param IObjectSetWithLoadCriteriaSupport $entities
     * @param null                              $labelMemberExpression
     *
     * @param ICriteria                         $criteria
     *
     * @return array|\Dms\Core\Form\IFieldOption[]
     */
    private function loadOptionsViaOptimizedLoadCriteria(
            IObjectSetWithLoadCriteriaSupport $entities,
            $labelMemberExpression = null,
            ICriteria $criteria = null
    ) : array
    {
        /** @var LoadCriteria $loadCriteria */
        $loadCriteria = $entities->loadCriteria();

        if ($criteria) {
            $loadCriteria = $loadCriteria->merge($criteria);
        }

        $loadCriteria->load(Entity::ID, 'id');
        if ($labelMemberExpression) {
            $loadCriteria->load($labelMemberExpression, 'label');
        }

        $options = [];

        foreach ($entities->loadMatching($loadCriteria) as $item) {
            $options[] = new FieldOption(
                    $item['id'],
                    (string)(isset($item['label']) ? $item['label'] : $item['id'])
            );
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllValues() : array
    {
        if ($this->objects instanceof IObjectSetWithLoadCriteriaSupport) {
            return array_column(
                    $this->objects->loadMatching(
                            $this->objects->loadCriteria()->load(Entity::ID)
                    ),
                    Entity::ID
            );
        }

        return parent::getAllValues();
    }

    /**
     * @param int    $index
     * @param object $object
     *
     * @return int
     */
    protected function getObjectIdentity(int $index, $object) : int
    {
        /** @var IEntity $object */
        return $object->getId();
    }

    /**
     * Returns whether the options are filterable.
     *
     * @return bool
     */
    public function canFilterOptions() : bool
    {
        return count($this->filterByMemberExpressions) > 0;
    }

    /**
     * @inheritDoc
     */
    public function getFilteredOptions(string $filter) : array
    {
        if (empty($this->filterByMemberExpressions)) {
            throw InvalidOperationException::methodCall(__METHOD__, 'filtering is not supported');
        }

        $criteria = $this->objects->criteria()->whereAny(function (SpecificationDefinition $match) use ($filter) {
            foreach ($this->filterByMemberExpressions as $expression) {

                $type = $match->getMemberExpressionParser()->parse(
                        $match->getClass(),
                        $expression
                )->getResultingType()->nonNullable();

                if ($type->supportsOperator(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE)) {
                    $match->whereStringContainsCaseInsensitive($expression, $filter);
                } elseif ($type instanceof ObjectType && $type->isSubsetOf(ValueObject::type())) {
                    /** @var string|ValueObject $valueObjectClass */
                    $valueObjectClass = $type->getClass();
                    foreach ($valueObjectClass::definition()->getProperties() as $property) {
                        if ($property->getType()->supportsOperator(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE)) {
                            $match->whereStringContainsCaseInsensitive($expression . '.' . $property->getName(), $filter);
                        }
                    }
                } else {
                    throw InvalidArgumentException::format(
                            'Invalid filter member expression found: \'%s\', cannot be filtered by string',
                            $expression
                    );
                }
            }
        });
        
        if ($this->canLoadViaOptimizedCriteria()) {
            return $this->loadOptionsViaOptimizedLoadCriteria($this->objects, $this->labelMemberExpression, $criteria);
        } else {
            $objects = $this->objects->matching($criteria);
            $options = [];
            
            foreach ($objects as $object) {
                $options[] = $this->getFieldOptionForObject($object, $object->getId());
            }
            
            return $options;
        }
    }

    /**
     * @param string[] $memberExpressions
     *
     * @return static
     */
    public function withFilterByMemberExpressions(array $memberExpressions)
    {
        $clone = clone $this;

        $clone->filterByMemberExpressions = $memberExpressions;

        return $clone;
    }

    /**
     * @return bool
     */
    protected function canLoadViaOptimizedCriteria() : bool
    {
        return $this->objects instanceof IObjectSetWithLoadCriteriaSupport && $this->labelMemberExpression && !$this->enabledCallback;
    }
}