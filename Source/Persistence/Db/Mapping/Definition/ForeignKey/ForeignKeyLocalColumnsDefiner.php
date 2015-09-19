<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyLocalColumnsDefiner extends ForeignKeyDefinerBase
{
    /**
     * Defines the columns on the table to be part of the foreign key.
     *
     * @param string|string[] $columnNames
     *
     * @return ForeignKeyReferencedColumnsDefiner
     */
    public function columns($columnNames)
    {
        return new ForeignKeyReferencedColumnsDefiner(
                $this->callback,
                is_array($columnNames) ? $columnNames : [$columnNames]
        );
    }
}