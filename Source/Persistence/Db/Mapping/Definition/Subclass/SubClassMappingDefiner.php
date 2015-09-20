<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\Subclass;

use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\EmbeddedObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Hierarchy\JoinedTableObjectMapping;
use Iddigital\Cms\Core\Persistence\Db\Schema\Table;

/**
 * The subclass definition definer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SubClassMappingDefiner extends SubClassDefinerBase
{
    /**
     * Defines the subclass to be embedded in the parent table
     * and will store a marker value within a column of the class type.
     *
     * This is the using the single table inheritance pattern.
     *
     * @param string $columnName
     * @param mixed  $classTypeValue
     *
     * @return SubClassDefinitionDefiner
     */
    public function withTypeInColumn($columnName, $classTypeValue)
    {
        return new SubClassDefinitionDefiner(
                $this->parentDefinition,
                $this->subClassDefinition,
                function (MapperDefinition $subClassDefinition) use ($columnName, $classTypeValue) {
                    foreach ($subClassDefinition->getColumns() as $column) {
                        $this->parentDefinition->addColumn($column->asNullable());
                    }
                    $subClassDefinition->setColumns($this->parentDefinition->getColumns());

                    call_user_func(
                            $this->callback,
                            function (Table $parentTable) use ($subClassDefinition, $columnName, $classTypeValue) {
                                $finalizedSubClassDefinition = $subClassDefinition->finalize($parentTable->getName());

                                return new EmbeddedObjectMapping(
                                        $parentTable,
                                        $finalizedSubClassDefinition,
                                        $columnName,
                                        $classTypeValue
                                );
                            }
                    );
                }
        );
    }

    /**
     * Defines the subclass to be stored in a separate table
     * with the primary key of that table as a foreign key referencing
     * the parent table primary key.
     *
     * This is the using the class table inheritance pattern.
     *
     * @param string $tableName
     *
     * @return SubClassDefinitionDefiner
     */
    public function asSeparateTable($tableName)
    {
        return new SubClassDefinitionDefiner(
                $this->parentDefinition,
                $this->subClassDefinition,
                function (MapperDefinition $subClassDefinition) use ($tableName) {
                    $finalizedSubClassDefinition = $subClassDefinition->finalize($tableName);

                    call_user_func(
                            $this->callback,
                            function (Table $parentTable) use ($finalizedSubClassDefinition) {
                                return new JoinedTableObjectMapping(
                                        $parentTable,
                                        $finalizedSubClassDefinition
                                );
                            }
                    );
                }
        );
    }
}