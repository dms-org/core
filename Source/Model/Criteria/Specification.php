<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception;
use Iddigital\Cms\Core\Model\Criteria\Condition\AndCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\NotCondition;
use Iddigital\Cms\Core\Model\Criteria\Condition\OrCondition;
use Iddigital\Cms\Core\Model\ISpecification;
use Iddigital\Cms\Core\Model\ITypedObject;
use Iddigital\Cms\Core\Model\Object\TypedObject;

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
    private $filterCallable;

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

        $this->filterCallable = $this->condition->getFilterCallable();
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
     * Returns whether the object satisfies the specification.
     *
     * @param ITypedObject $object
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedBy(ITypedObject $object)
    {
        $type = $this->type;
        if (!($object instanceof $type)) {
            throw Exception\TypeMismatchException::argument(__METHOD__, 'object', $type, $object);
        }

        return call_user_func($this->filterCallable, $object);
    }
}