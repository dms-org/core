<?php

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\AndCondition;
use Dms\Core\Model\Criteria\Condition\NotCondition;
use Dms\Core\Model\Criteria\Condition\OrCondition;
use Dms\Core\Model\ISpecification;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\TypedObject;

/**
 * The typed object specification class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class Specification extends ObjectCriteriaBase implements ISpecification
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var callable
     */
    private $arrayFilterCallable;

    /**
     * Specification constructor.
     */
    public function __construct()
    {
        $this->type = $this->type();

        if (!is_subclass_of($this->type, TypedObject::class, true)) {
            throw Exception\InvalidArgumentException::format(
                    'Invalid object type for %s specification: must be a subclass of %s, %s given',
                    get_class($this), TypedObject::class, $this->type
            );
        }

        /** @var string|TypedObject $type */
        $type = $this->type;
        parent::__construct($type::definition());
        $definition = new SpecificationDefinition($this->class);
        $this->define($definition);

        $this->condition = $definition->getCondition();

        if (!$this->condition) {
            throw Exception\InvalidOperationException::format(
                    'Invalid specification definition for %s: no conditions defined',
                    get_class($this)
            );
        }

        $this->arrayFilterCallable = $this->condition->getArrayFilterCallable();
    }

    /**
     * Returns the class name for the object to which the specification applies.
     *
     * @return string
     */
    abstract protected function type();

    /**
     * Defines the criteria for the specification.
     *
     * @param SpecificationDefinition $match
     *
     * @return void
     */
    abstract protected function define(SpecificationDefinition $match);

    /**
     * {@inheritDoc}
     */
    final public function asCriteria()
    {
        return (new Criteria($this->getClass()))->whereSatisfies($this);
    }

    /**
     * {@inheritDoc}
     */
    final public function and_(ISpecification $specification)
    {
        $specification->verifyOfClass($this->class->getClassName());

        return new CustomSpecification(
                $this->class->getClassName(),
                function (SpecificationDefinition $match) use ($specification) {
                    $match->condition = new AndCondition([
                            $this->condition,
                            $specification->getCondition()
                    ]);
                }
        );
    }

    /**
     * {@inheritDoc}
     */
    final public function or_(ISpecification $specification)
    {
        $specification->verifyOfClass($this->class->getClassName());

        return new CustomSpecification(
                $this->class->getClassName(),
                function (SpecificationDefinition $match) use ($specification) {
                    $match->condition = new OrCondition([
                            $this->condition,
                            $specification->getCondition()
                    ]);
                }
        );
    }

    /**
     * {@inheritDoc}
     */
    final public function not()
    {
        return new CustomSpecification(
                $this->class->getClassName(),
                function (SpecificationDefinition $match) {
                    $match->condition = new NotCondition($this->condition);
                }
        );
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedBy(ITypedObject $object)
    {
        return count($this->filter([$object])) === 1;
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedByAll(array $objects)
    {
        return count($this->filter($objects)) === count($objects);
    }

    /**
     * @inheritDoc
     */
    public function isSatisfiedByAny(array $objects)
    {
        return count($this->filter($objects)) > 0;
    }

    /**
     * @inheritDoc
     */
    public function filter(array $objects)
    {
        Exception\TypeMismatchException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->type);

        return call_user_func($this->arrayFilterCallable, $objects);
    }
}