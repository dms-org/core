<?php declare(strict_types = 1);

namespace Dms\Core\Model;

use Dms\Core\Exception;
use Dms\Core\Model\Criteria\Condition\Condition;
use Dms\Core\Model\Criteria\NestedMember;
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
    public function getClass() : FinalizedClassDefinition;

    /**
     * Throws an exception if the criteria of not for the supplied class.
     *
     * @param string $class
     *
     * @return void
     * @throws Exception\TypeMismatchException
     */
    public function verifyOfClass(string $class);

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
    public function asCriteria() : ICriteria;

    /**
     * Returns a specification that is satisfied if BOTH specifications
     * are met.
     *
     * @param ISpecification $specification
     *
     * @return ISpecification
     * @throws Exception\TypeMismatchException
     */
    public function and_(ISpecification $specification) : ISpecification;

    /**
     * Returns a specification that is satisfied if EITHER specifications
     * are met.
     *
     * @param ISpecification $specification
     *
     * @return ISpecification
     * @throws Exception\TypeMismatchException
     */
    public function or_(ISpecification $specification) : ISpecification;

    /**
     * Returns a specification that is satisfied if the current specification
     * is not met.
     *
     * @return ISpecification
     */
    public function not() : ISpecification;

    /**
     * Returns a specification that is satisfied the member of the supplied class
     * matches the current specification.
     * 
     * @param FinalizedClassDefinition $rootClass
     * @param NestedMember             $member
     *
     * @return ISpecification
     */
    public function forMemberOf(FinalizedClassDefinition $rootClass, NestedMember $member) : ISpecification;

    /**
     * Returns whether the object satisfies the specification.
     *
     * @param ITypedObject $object
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedBy(ITypedObject $object) : bool;

    /**
     * Returns whether all the object satisfies the specification.
     *
     * @param ITypedObject[] $objects
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedByAll(array $objects) : bool;

    /**
     * Returns whether all the object satisfies the specification.
     *
     * @param ITypedObject[] $objects
     *
     * @return bool
     * @throws Exception\TypeMismatchException
     */
    public function isSatisfiedByAny(array $objects) : bool;

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
    public function filter(array $objects) : array;
}
