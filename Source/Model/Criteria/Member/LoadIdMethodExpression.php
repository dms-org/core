<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The load id method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadIdMethodExpression extends LoadIdFromEntitySetMethodExpression
{
    const METHOD_NAME = 'load';

    /**
     * @inheritDoc
     */
    public function __construct(IType $sourceType, NestedMember $member, IEntitySet $dataSource)
    {
        parent::__construct($sourceType, self::METHOD_NAME, $member, $dataSource);

        if (!$member->getResultingType()->isSubsetOf(Type::int())) {
            throw InvalidArgumentException::format(
                    'Invalid argument for \'%s\' method, argument \'%s\' must result in type int, %s given',
                    self::METHOD_NAME, $member->asString(), $member->getResultingType()->asTypeString()
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
            $relatedIds = $memberGetter($objects);
            $idsToLoad  = [];

            foreach ($relatedIds as $key => $id) {
                if ($id !== null) {
                    $idsToLoad[$key] = $id;
                }
            }

            $relatedEntities = $this->dataSource->getAllById($idsToLoad);

            $idKeyMap = array_flip($idsToLoad);
            foreach ($relatedEntities as $relatedEntity) {
                $relatedIds[$idKeyMap[$relatedEntity->getId()]] = $relatedEntity;
            }

            return $relatedIds;
        };
    }
}