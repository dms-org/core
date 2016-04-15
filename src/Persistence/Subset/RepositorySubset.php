<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Subset;

use Dms\Core\Exception;
use Dms\Core\Model\ICriteria;
use Dms\Core\Model\Subset\MutableObjectSetSubset;
use Dms\Core\Persistence\IRepository;

/**
 * The entity object subset class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class RepositorySubset extends MutableObjectSetSubset implements IRepository
{
    /**
     * @var IRepository
     */
    protected $fullObjectSet;

    /**
     * @inheritDoc
     */
    public function __construct(IRepository $fullObjectSet, ICriteria $criteria)
    {
        parent::__construct($fullObjectSet, $criteria);
    }

    /**
     * @inheritdoc
     */
    public function getEntityType() : string
    {
        return $this->fullObjectSet->getEntityType();
    }
}