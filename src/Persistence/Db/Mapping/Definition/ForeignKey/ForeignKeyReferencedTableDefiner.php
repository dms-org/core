<?php

namespace Dms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyReferencedTableDefiner extends ForeignKeyDefinerBase
{
    /**
     * Defines the referenced table on the foreign key
     *
     * @param string $referencedTableName
     *
     * @return ForeignKeyOnDeleteModeDefiner
     */
    public function on($referencedTableName)
    {
        return new ForeignKeyOnDeleteModeDefiner(
                $this->callback,
                $this->localColumnNames,
                $this->referencedColumnNames,
                $referencedTableName
        );
    }
}