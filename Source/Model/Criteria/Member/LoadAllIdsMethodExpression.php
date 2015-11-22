<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\EntityCollection;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\WithElementsType;

/**
 * The load all ids method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadAllIdsMethodExpression extends LoadIdFromEntitySetMethodExpression
{
    const METHOD_NAME = 'loadAll';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member, IEntitySet $dataSource)
    {
        parent::__construct($sourceType, self::METHOD_NAME, $member, $dataSource, Type::collectionOf($dataSource->getElementType()));

        $idCollectionType = $member->getResultingType();
        if (!($idCollectionType instanceof WithElementsType) || !$idCollectionType->getElementType()->nonNullable()->isSubsetOf(Type::int())) {
            throw InvalidArgumentException::format(
                    'Invalid call to method \'%s\', argument \'%s\' must result in collection type of int, %s given',
                    self::METHOD_NAME, $member->asString(), $idCollectionType->asTypeString()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function createArrayGetterCallable()
    {
        $memberGetter = $this->member->makeArrayGetterCallable();

        return function (array $objects) use ($memberGetter) {
            $relatedIdGroups     = $memberGetter($objects);
            $relatedEntityGroups = [];
            $idsToLoad           = [];
            $idKeyMap            = array_flip($idsToLoad);

            foreach ($relatedIdGroups as $key => $ids) {
                if ($ids !== null) {
                    $relatedEntityGroups[$key] = [];

                    foreach ($ids as $id) {
                        $idsToLoad[]     = $id;
                        $idKeyMap[$id][] = $key;
                    }
                } else {
                    $relatedEntityGroups[$key] = null;
                }
            }

            $relatedEntities = $this->dataSource->getAllById($idsToLoad);

            foreach ($relatedEntities as $relatedEntity) {
                foreach ($idKeyMap[$relatedEntity->getId()] as $key) {
                    $relatedEntityGroups[$key][] = $relatedEntity;
                }
            }

            $entityType = $this->dataSource->getEntityType();

            foreach ($relatedEntityGroups as $key => $group) {
                $relatedEntityGroups[$key] = new EntityCollection(
                        $entityType,
                        $group
                );
            }

            return $relatedEntityGroups;
        };
    }
}