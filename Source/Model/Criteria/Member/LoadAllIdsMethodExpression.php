<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\WithElementsType;

/**
 * The load all ids method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadAllIdsMethodExpression extends MethodExpression
{
    const METHOD_NAME = 'loadAll';

    /**
     * @var NestedMember
     */
    protected $member;

    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member, IEntitySet $dataSource)
    {
        parent::__construct($sourceType, self::METHOD_NAME, [$member->asString()], $dataSource->getElementType());
        $this->dataSource = $dataSource;
        $this->member     = $member;

        if (!($member->getResultingType() instanceof WithElementsType)) {
            throw InvalidArgumentException::format(
                    'Invalid member supplied to %s: \'%s\' must result in collection type, %s given',
                    __METHOD__, $member->asString(), $member->getResultingType()->asTypeString()
            );
        }
    }


    /**
     * @inheritDoc
     */
    public function isPropertyValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getProperty()
    {
        return null;
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

            return $relatedIdGroups;
        };
    }
}