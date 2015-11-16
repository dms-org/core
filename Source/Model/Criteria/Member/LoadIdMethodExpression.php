<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Model\Criteria\NestedMember;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\ObjectType;

/**
 * The load id method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadIdMethodExpression extends MethodExpression
{
    const METHOD_NAME = 'load';

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
            $relatedIds = $memberGetter($objects);
            $idsToLoad  = [];

            foreach ($relatedIds as $key => $id) {
                if ($id !== null) {
                    $idsToLoad[$key] = $id;
                }
            }

            $relatedEntities = $this->dataSource->getAllById($idsToLoad);

            $idKeyMap        = array_flip($idsToLoad);
            foreach ($relatedEntities as $relatedEntity) {
                $relatedIds[$idKeyMap[$relatedEntity->getId()]] = $relatedEntity;
            }

            return $relatedIds;
        };
    }
}