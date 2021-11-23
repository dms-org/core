<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Relation;

use Dms\Core\Persistence\Db\Mapping\Definition\IncompatiblePropertyMappingException;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;

/**
 * The relation mapping base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class RelationMapping
{
    /**
     * @var IAccessor
     */
    protected $accessor;

    /**
     * @var IRelation
     */
    protected $relation;

    /**
     * RelationMapping constructor.
     *
     * @param IAccessor $accessor
     * @param IRelation $relation
     * @param bool      $ignoreNullabilityMismatch
     * @param bool      $ignoreTypeMismatch
     *
     * @throws IncompatiblePropertyMappingException
     */
    public function __construct(IAccessor $accessor, IRelation $relation, bool $ignoreNullabilityMismatch = false, bool $ignoreTypeMismatch = null)
    {
        $accessorType = $accessor->getCompatibleType();
        $relationType = $relation->getValueType();

        if ($accessorType !== null && !$ignoreTypeMismatch) {
            if ($ignoreNullabilityMismatch) {
                $accessorType = $accessorType->nonNullable();
                $relationType = $relationType->nonNullable();
            }

            if (!$accessorType->equals($relationType)) {
                throw IncompatiblePropertyMappingException::format(
                        'Invalid property to relation mapping: cannot bind %s to relation of type %s as the types are incompatible, '
                        . 'property type %s must match the relation type %s',
                        $accessor->getDebugName(), get_class($relation),
                        $accessorType->asTypeString(), $relationType->asTypeString()
                );
            }
        }

        $this->accessor = $accessor;
        $this->relation = $relation;
    }

    /**
     * @return IAccessor
     */
    public function getAccessor() : IAccessor
    {
        return $this->accessor;
    }

    /**
     * @return IRelation
     */
    public function getRelation() : IRelation
    {
        return $this->relation;
    }

    /**
     * @param string $prefix
     *
     * @return static
     */
    public function withEmbeddedColumnsPrefixedBy(string $prefix)
    {
        $clone = clone $this;

        $clone->relation = $this->relation->withEmbeddedColumnsPrefixedBy($prefix);

        return $clone;
    }
}