<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\FinalizedMapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

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
    public function __construct(Table $parentTable, FinalizedMapperDefinition $definition, $dependencyMode, array $mappingTables, array $columnsToLoad = [])
    {
        parent::__construct($definition, $dependencyMode, $mappingTables, $columnsToLoad);

        $this->parentTable = $parentTable;
    }
}