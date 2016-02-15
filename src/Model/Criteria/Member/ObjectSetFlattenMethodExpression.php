<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\ObjectCollection;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\IType;
use Dms\Core\Model\Type\ObjectType;
use Dms\Core\Model\Type\WithElementsType;
use Dms\Core\Model\TypedCollection;

/**
 * The object set flatten method expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectSetFlattenMethodExpression extends ObjectSetMethodExpression
{
    const METHOD_NAME = 'flatten';

    /**
     * @var NestedMember
     */
    protected $member;

    /**
     * @var bool
     */
    protected $isArray;

    /**
     * @var IType
     */
    protected $elementType;

    /**
     * @var string|null
     */
    protected $objectType;

    /**
     * @param IType        $sourceType
     * @param NestedMember $member
     *
     * @throws InvalidArgumentException
     */
    public function __construct(IType $sourceType, NestedMember $member)
    {
        $memberType = $member->getResultingType()->nonNullable();

        if (!($memberType instanceof WithElementsType)) {
            throw InvalidArgumentException::format(
                    'Invalid member supplied to %s: must be a collection or array, %s given',
                    __METHOD__, $memberType->asTypeString()
            );
        }

        parent::__construct($sourceType, self::METHOD_NAME, [$member->asString()], $memberType);

        $this->member      = $member;
        $this->isArray     = $memberType instanceof ArrayType;
        $this->elementType = $memberType->getElementType();
        $this->objectType  = $this->elementType instanceof ObjectType
                ? $this->elementType->getClass()
                : null;
    }

    /**
     * @return NestedMember
     */
    public function getMember() : \Dms\Core\Model\Criteria\NestedMember
    {
        return $this->member;
    }

    /**
     * @inheritDoc
     */
    public function isPropertyValue() : bool
    {
        return $this->member->isPropertyValue();
    }

    /**
     * @inheritDoc
     */
    public function getProperty()
    {
        return $this->member->getProperty();
    }

    /**
     * @return \Closure
     */
    public function createArrayGetterCallable() : callable
    {
        $memberGetter = $this->member->makeArrayGetterCallable();

        return function (array $objectSets) use ($memberGetter) {
            $results = [];

            foreach ($objectSets as $key => $objectSet) {
                /** @var IObjectSet|null $objectSet */

                if ($objectSet === null) {
                    $results[$key] = null;
                    continue;
                }

                $nestedCollections = $memberGetter($objectSet->getAll());
                $flattenedValues   = [];

                foreach ($nestedCollections as $nestedCollection) {
                    if ($nestedCollection !== null) {
                        foreach ($nestedCollection as $value) {
                            $flattenedValues[] = $value;
                        }
                    }
                }

                if ($this->isArray) {
                    $results[$key] = $flattenedValues;
                } else {
                    $results[$key] = $this->objectType
                            ? new ObjectCollection($this->objectType, $flattenedValues)
                            : new TypedCollection($this->elementType, $flattenedValues);
                }
            }

            return $results;
        };
    }
}