<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

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
        parent::__construct($sourceType, self::METHOD_NAME, $member, $dataSource, $dataSource->getElementType()->nullable());

        if (!$member->getResultingType()->nonNullable()->isSubsetOf(Type::int())) {
            throw InvalidArgumentException::format(
                'Invalid argument for \'%s\' method, argument \'%s\' must result in type int, %s given',
                self::METHOD_NAME, $member->asString(), $member->getResultingType()->asTypeString()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function createArrayGetterCallable() : callable
    {
        $memberGetter = $this->member->makeArrayGetterCallable();

        return function (array $objects) use ($memberGetter) {
            $relatedIds = $memberGetter($objects);
            $idsToLoad  = [];
            $idKeyMap   = [];

            foreach ($relatedIds as $key => $id) {
                if ($id !== null) {
                    $idsToLoad[$key] = $id;
                    $idKeyMap[$id][] = $key;
                }
            }

            $relatedEntities = $this->dataSource->getAllById($idsToLoad);

            foreach ($relatedEntities as $relatedEntity) {
                foreach ($idKeyMap[$relatedEntity->getId()] as $key) {
                    $relatedIds[$key] = $relatedEntity;
                }
            }

            return $relatedIds;
        };
    }
}