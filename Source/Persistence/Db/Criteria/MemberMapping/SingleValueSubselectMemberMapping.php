<?php

namespace Iddigital\Cms\Core\Persistence\Db\Criteria\MemberMapping;

use Iddigital\Cms\Core\Persistence\Db\Query\Expression\SubSelect;

/**
 * The sub-select member mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SingleValueSubselectMemberMapping extends MemberMapping
{
    /**
     * @var SubSelect
     */
    protected $select;

    /**
     * SingleValueSubselectMemberMapping constructor.
     *
     * @param SubSelect $select
     */
    public function __construct(SubSelect $select)
    {
        $this->select = $select;
    }

    /**
     * @return SubSelect
     */
    public function getSelect()
    {
        return $this->select;
    }
}