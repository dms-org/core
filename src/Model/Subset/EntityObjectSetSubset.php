<?php declare(strict_types = 1);

namespace Dms\Core\Model\Subset;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\IEntitySet;

/**
 * The entity object subset class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityObjectSetSubset extends IdentifiableObjectSetSubset implements IEntitySet
{
    /**
     * @var IEntitySet
     */
    protected $fullObjectSet;

    /**
     * @inheritDoc
     */
    public function __construct(IEntitySet $fullObjectSet, ICriteria $criteria)
    {
        parent::__construct($fullObjectSet, $criteria);
    }

    /**
     * Returns the entity type of the entity set.
     *
     * @return string
     */
    public function getEntityType() : string
    {
        return $this->fullObjectSet->getEntityType();
    }
}