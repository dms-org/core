<?php

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The object specification interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface ISpecification
{
    /**
     * Returns the object type of the criteria.
     *
     * @return FinalizedClassDefinition
     */
    public function getClass();

    /**
     * Throws an exception if the criteria of not for the supplied class.
     *
     * @param string $class
     *
     * @return void
     * @throws Exception\TypeMismatchException
     */
    public function verifyOfClass($class);

    /**
     * Gets the conditions defining of the object.
     *
     * @return Condition
     */
    public function getCondition();

    /**
     * Returns the specification as an equivalent criteria object.
     *
     * @return ICriteria
     */
    public function asCriteria();

    /**
     * Returns a specification that is satisfied if BOTH specifications
     * are met.
     *
     * @param ISpecification $specification
     *
     * @return ISpecification
     * @throws Exception\TypeMismatchException
     */
    public function and_(ISpecification $specification);

    /**
     * Returns a specification that is satisfied if EITHER specifications
     * are met.
     *
     * @param ISpecification $specification
     *
     * @return ISpecification
     * @throws Exception\TypeMismatchException
     */
    public function or_(ISpecification $specification);

    /**
     * Returns a specification that is satisfied if the current specification
     * is not met.
     *
     * @return ISpecification
     */
    public function not();

    /**
     * Returns whether the object satisfies the specification.
     *
     * @param ITypedObject $object
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedBy(ITypedObject $object);

    /**
     * Returns whether all the object satisfies the specification.
     *
     * @param ITypedObject[] $objects
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedByAll(array $objects);

    /**
     * Returns whether all the object satisfies the specification.
     *
     * @param ITypedObject[] $objects
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedByAny(array $objects);

    /**
     * Returns only the objects which is satisfied by this specification.
     *
     * NOTE: keys are maintained
     *
     * @param ITypedObject[] $objects
     *
     * @return ITypedObject[]
     * @throws Exception\TypeMismatchException
     */
    public function filter(array $objects);
}
