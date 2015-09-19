<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyReferencedColumnsDefiner extends ForeignKeyDefinerBase
{
    /**
     * Defines the referenced columns on the foreign key.
     *
     * @param string|string[] $columnNames
     *
     * @return ForeignKeyReferencedTableDefiner
     */
    public function references($columnNames)
    {
        return new ForeignKeyReferencedTableDefiner(
                $this->callback,
                $this->localColumnNames,
                is_array($columnNames) ? $columnNames : [$columnNames]
        );
    }
}