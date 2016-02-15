<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Criteria;

use Dms\Core\Exception\BaseException;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\IMemberExpression;
use Dms\Core\Model\Criteria\Member\CollectionCountMethodExpression;
use Dms\Core\Model\Criteria\Member\LoadIdFromEntitySetMethodExpression;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetAggregateMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetAverageMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetFlattenMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetMaximumMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetMinimumMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetSumMethodExpression;
use Dms\Core\Model\Criteria\Member\SelfExpression;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Object\FinalizedPropertyDefinition;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ColumnMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\MemberMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationAggregateMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationCountMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEntityRelationMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneIdRelationMapping;
use Dms\Core\Persistence\Db\Mapping\IEntityMapper;
use Dms\Core\Persistence\Db\Mapping\IObjectMapper;
use Dms\Core\Persistence\Db\Mapping\Relation\Embedded\EmbeddedObjectRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\EntityRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\IToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\NonIdentifyingRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\RelationObjectReference;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Dms\Core\Persistence\Db\Mapping\Relation\ToOneRelation;
use Dms\Core\Persistence\Db\Query;
use Dms\Core\Persistence\Db\Query\Expression\SimpleAggregate;
use Dms\Core\Util\Debug;

/**
 * The member expression mapper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionMapper
{
    const SELF_RELATION_ID = 'self-relation';

    /**
     * @var IEntityMapper
     */
    private $rootEntityMapper;

    /**
     * MemberExpressionMapper constructor.
     *
     * @param IEntityMapper $mapper
     */
    public function __construct(IEntityMapper $mapper)
    {
        $this->rootEntityMapper = $mapper;
    }

    /**
     * Maps the supplied member expression.
     *
     * @param NestedMember $member
     *
     * @return MemberMapping
     * @throws MemberExpressionMappingException
     */
    public function mapMemberExpression(NestedMember $member) : MemberMapping
    {
        try {
            $nestedRelations = $this->mapMemberExpressionsToRelations(
                    $this->rootEntityMapper,
                    $member->getPartsExceptLast(),
                    $finalMapper
            );

            return $this->mapFinalMember($finalMapper, $nestedRelations, $member->getLastPart());
        } catch (BaseException $inner) {
            if ($inner instanceof MemberExpressionMappingException && $inner->getPrevious()) {
                $inner = $inner->getPrevious();
            }

            throw new MemberExpressionMappingException(
                    sprintf(
                            'Could not map member expression \'%s\' of entity type %s: %s',
                            $member->asString(), $this->rootEntityMapper->getObjectType(), $inner->getMessage()
                    ),
                    null,
                    $inner
            );
        }
    }

    /**
     * @param IEntityMapper $mapper
     * @param callable      $callback
     *
     * @return mixed
     */
    protected function withRootEntityMapper(IEntityMapper $mapper, callable $callback)
    {
        $oldMapper              = $this->rootEntityMapper;
        $this->rootEntityMapper = $mapper;
        $result                 = $callback();
        $this->rootEntityMapper = $oldMapper;

        return $result;
    }

    /**
     * @param IObjectMapper     $mapper
     * @param IRelation[]       $nestedRelations
     * @param IMemberExpression $lastPart
     *
     * @return MemberMapping
     * @throws InvalidArgumentException
     */
    protected function mapFinalMember(IObjectMapper $mapper, array $nestedRelations, IMemberExpression $lastPart) : MemberMapping
    {
        switch (true) {
            case $lastPart instanceof SelfExpression:
                /** @var IEntityMapper $mapper */
                return $this->mapFinalSelfRelation($mapper);

            case $lastPart instanceof MemberPropertyExpression:
                return $this->mapFinalPropertyToMapping($nestedRelations, $lastPart->getProperty());

            case $lastPart instanceof LoadIdFromEntitySetMethodExpression:
                $relations    = $this->mapLoadExpressionToRelations($mapper, $lastPart);
                $lastRelation = array_pop($relations);

                return $this->mapFinalRelationToMapping(array_merge($nestedRelations, $relations), $lastRelation);

            case $lastPart instanceof CollectionCountMethodExpression:
                $lastRelation = array_pop($nestedRelations);

                return new ToManyRelationCountMapping($this->rootEntityMapper, $nestedRelations, $lastRelation);

            case $lastPart instanceof ObjectSetAggregateMethodExpression:
                $lastRelation = array_pop($nestedRelations);

                return $this->mapFinalAggregateExpression($nestedRelations, $lastRelation, $lastPart);

            case $lastPart instanceof ObjectSetFlattenMethodExpression:
                $relations    = $this->mapMemberExpressionsToRelations($mapper, [$lastPart]);
                $lastRelation = array_pop($relations);

                return $this->mapFinalRelationToMapping(array_merge($nestedRelations, $relations), $lastRelation);
        }

        throw InvalidArgumentException::format('unknown final member expression type %s', Debug::getType($lastPart));
    }

    /**
     * @param IRelation[] $nestedRelations
     *
     * @return IEntityMapper
     */
    protected function getFinalEntityMapper(array $nestedRelations) : IEntityMapper
    {
        $entityMapper = null;

        foreach (array_reverse($nestedRelations) as $relation) {
            if ($relation instanceof EntityRelation) {
                $entityMapper = $relation->getEntityMapper();
                break;
            }
        }

        return $entityMapper ?: $this->rootEntityMapper;
    }

    protected function mapFinalPropertyToMapping(array $nestedRelations, FinalizedPropertyDefinition $property)
    {
        $propertyName = $property->getName();

        /** @var IRelation $lastRelation */
        $lastRelation = end($nestedRelations);
        $definition   = $lastRelation ? $lastRelation->getMapper()->getDefinition() : $this->rootEntityMapper->getDefinition();

        if (isset($definition->getPropertyColumnMap()[$propertyName])) {
            $columnName                  = $definition->getPropertyColumnMap()[$propertyName];
            $phpToDbPropertyConverterMap = $definition->getPhpToDbPropertyConverterMap();

            return new ColumnMapping(
                    $this->rootEntityMapper,
                    $nestedRelations,
                    $definition->getTable()->getColumn($columnName),
                    isset($phpToDbPropertyConverterMap[$propertyName]) ? $phpToDbPropertyConverterMap[$propertyName] : null
            );
        } elseif ($relation = $definition->getRelationMappedToProperty($propertyName)) {
            return $this->mapFinalRelationToMapping($nestedRelations, $relation);
        }

        throw InvalidArgumentException::format(
                'cannot map property \'%s\' of type %s, property is not mapped according to mapper definition for type %s',
                $propertyName, $property->getType()->asTypeString(), $definition->getClassName()
        );
    }

    /**
     * @param IRelation[] $nestedRelations
     * @param IRelation   $lastRelation
     *
     * @return MemberMapping
     */
    protected function mapFinalRelationToMapping(array $nestedRelations, IRelation $lastRelation) : MemberMapping
    {
        if ($lastRelation instanceof IToManyRelation) {
            return new ToManyRelationMapping($this->rootEntityMapper, $nestedRelations, $lastRelation);
        } elseif ($lastRelation instanceof EntityRelation) {
            if ($lastRelation->getReference() instanceof RelationObjectReference) {
                return new ToOneEntityRelationMapping($this->rootEntityMapper, $nestedRelations, $lastRelation);
            } else {
                return new ToOneIdRelationMapping($this->rootEntityMapper, $nestedRelations, $lastRelation);
            }
        } else {
            /** @var EmbeddedObjectRelation $lastRelation */
            return new ToOneEmbeddedObjectMapping($this->rootEntityMapper, $nestedRelations, $lastRelation);
        }
    }

    /**
     * @param IRelation[]                        $nestedRelations
     * @param IToManyRelation                    $lastRelation
     *
     * @param ObjectSetAggregateMethodExpression $lastPart
     *
     * @return MemberMapping
     * @throws InvalidArgumentException
     */
    protected function mapFinalAggregateExpression(
            array $nestedRelations,
            IToManyRelation $lastRelation,
            ObjectSetAggregateMethodExpression $lastPart
    ) : MemberMapping {
        switch (true) {
            case $lastPart instanceof ObjectSetAverageMethodExpression:
                $aggregateType = SimpleAggregate::AVG;
                break;

            case $lastPart instanceof ObjectSetSumMethodExpression:
                $aggregateType = SimpleAggregate::SUM;
                break;

            case $lastPart instanceof ObjectSetMaximumMethodExpression:
                $aggregateType = SimpleAggregate::MAX;
                break;

            case $lastPart instanceof ObjectSetMinimumMethodExpression:
                $aggregateType = SimpleAggregate::MIN;
                break;

            default:
                throw InvalidArgumentException::format('unknown aggregate expression type %s', Debug::getType($lastPart));
        }

        $argumentMapping = $this->withRootEntityMapper(
                $this->getFinalEntityMapper(array_merge($nestedRelations, [$lastRelation])),
                function () use ($lastPart) {
                    return $this->mapMemberExpression($lastPart->getAggregatedMember());
                }
        );

        return new ToManyRelationAggregateMapping(
                $this->rootEntityMapper,
                $nestedRelations,
                $lastRelation,
                $aggregateType,
                $argumentMapping
        );
    }

    /**
     * @param IObjectMapper       $mapper
     * @param IMemberExpression[] $memberExpressions
     * @param IObjectMapper|null  $finalMapper
     *
     * @return IRelation[]
     * @throws BaseException
     * @throws InvalidArgumentException
     */
    protected function mapMemberExpressionsToRelations(IObjectMapper $mapper, array $memberExpressions, IObjectMapper &$finalMapper = null) : array
    {
        $nestedRelations = [];

        foreach ($memberExpressions as $part) {
            if ($part instanceof ObjectSetFlattenMethodExpression) {
                $relationsToAdd = $this->mapMemberExpressionsToRelations($mapper, $part->getMember()->getParts());
            } //
            elseif ($part instanceof LoadIdFromEntitySetMethodExpression) {
                $relationsToAdd = $this->mapLoadExpressionToRelations($mapper, $part);
            } //
            elseif ($part instanceof MemberPropertyExpression) {
                $relationsToAdd = [$this->mapPropertyToRelation($mapper, $part)];
            } //
            else {
                throw InvalidArgumentException::format('invalid member part \'%s\', must be a relation expression', $part->asString());
            }

            $nestedRelations = array_merge($nestedRelations, $relationsToAdd);

            /** @var IRelation $lastRelation */
            $lastRelation = end($relationsToAdd);
            $mapper       = $lastRelation->getMapper();
        }

        $finalMapper = $mapper;

        return $nestedRelations;
    }

    /**
     * @param IObjectMapper                       $mapper
     * @param LoadIdFromEntitySetMethodExpression $part
     * @param IObjectMapper|null                  $finalMapper
     *
     * @return IRelation[]
     * @throws InvalidArgumentException
     */
    protected function mapLoadExpressionToRelations(IObjectMapper $mapper, LoadIdFromEntitySetMethodExpression $part, IObjectMapper &$finalMapper = null) : array
    {
        /** @var EntityRelation $relationToLoadAsObject */
        $innerRelations         = $this->mapMemberExpressionsToRelations($mapper, $part->getIdMember()->getParts(), $finalMapper);
        $relationToLoadAsObject = array_pop($innerRelations);

        $relationsToAdd   = $innerRelations;
        $relationsToAdd[] = $relationToLoadAsObject->withObjectReference();

        return $relationsToAdd;
    }

    /**
     * @param IObjectMapper            $mapper
     * @param MemberPropertyExpression $part
     *
     * @return IRelation
     * @throws BaseException
     */
    private function mapPropertyToRelation(IObjectMapper $mapper, MemberPropertyExpression $part) : IRelation
    {
        $definition = $mapper->getDefinition();

        $property = $part->getProperty();

        $relation = $definition->getRelationMappedToProperty($property->getName());

        if (!$relation) {
            throw BaseException::format(
                    'invalid property \'%s\' of type %s, must be mapped to a relation',
                    $property->getName(), $definition->getClassName()
            );
        }

        return $relation;
    }

    /**
     * @param IEntityMapper $mapper
     *
     * @return MemberMapping
     */
    protected function mapFinalSelfRelation(IEntityMapper $mapper) : MemberMapping
    {
        return $this->mapFinalRelationToMapping(
                [],
                new ToOneRelation(
                        self::SELF_RELATION_ID,
                        new ToOneRelationObjectReference($mapper),
                        $mapper->getDefinition()->getTable()->getPrimaryKeyColumnName(),
                        new NonIdentifyingRelationMode()
                )
        );
    }
}