<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Hierarchy;

use Dms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Dms\Core\Persistence\Db\Schema\Table;

/**
 * The subclass object mapping class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class SubClassObjectMapping extends ObjectMapping
{
    /**
     * @var Table
     */
    protected $parentTable;

    /**
     * @inheritDoc
     */
    public function __construct(Table $parentTable, FinalizedMapperDefinition $definition, $dependencyMode)
    {
        parent::__construct($definition, $dependencyMode);

        $this->parentTable = $parentTable;
    }
}