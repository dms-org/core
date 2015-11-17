<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEntityMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IObjectMapper;
use Iddigital\Cms\Core\Persistence\Db\Query\Expression\SubSelect;

/**
 * The related object member mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RelatedObjectMemberMapping extends MemberMapping
{
    /**
     * @var IObjectMapper
     */
    protected $mapper;

    /**
     * @var SubSelect
     */
    protected $select;

    /**
     * RelatedObjectMemberMapping constructor.
     *
     * @param IObjectMapper $mapper
     * @param SubSelect     $select
     */
    public function __construct(IObjectMapper $mapper, SubSelect $select)
    {
        $this->mapper = $mapper;
        $this->select = $select;
    }

    /**
     * @return SubSelect
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return IObjectMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return bool
     */
    public function isValueObject()
    {
        return $this->mapper instanceof IEmbeddedObjectMapper;
    }

    /**
     * @return bool
     */
    public function isEntity()
    {
        return $this->mapper instanceof IEntityMapper;
    }
}