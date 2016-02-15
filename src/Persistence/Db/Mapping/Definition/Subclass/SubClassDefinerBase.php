<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Subclass;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The sub class definer base.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SubClassDefinerBase
{
    /**
     * @var IOrm
     */
    protected $orm;

    /**
     * @var MapperDefinition
     */
    protected $parentDefinition;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * SubClassDefinerBase constructor.
     *
     * @param IOrm             $orm
     * @param MapperDefinition $parentDefinition
     * @param callable         $callback
     */
    public function __construct(IOrm $orm, MapperDefinition $parentDefinition, callable $callback)
    {
        $this->parentDefinition = $parentDefinition;
        $this->callback         = $callback;
        $this->orm              = $orm;
    }

    /**
     * @return MapperDefinition
     */
    protected function constructSubclassDefinition() : \Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition
    {
        return new MapperDefinition($this->orm, $this->parentDefinition);
    }
}