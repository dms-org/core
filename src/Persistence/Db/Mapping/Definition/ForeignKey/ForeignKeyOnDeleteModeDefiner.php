<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyOnDeleteModeDefiner extends ForeignKeyDefinerBase
{
    /**
     * Defines the foreign key to delete the rows when the referenced
     * rows are deleted.
     *
     * @return ForeignKeyOnUpdateModeDefiner
     */
    public function onDeleteCascade() : ForeignKeyOnUpdateModeDefiner
    {
        return $this->onDelete(ForeignKeyMode::CASCADE);
    }

    /**
     * Defines the foreign key to set the columns to null
     * when the referenced rows are deleted.
     *
     * @return ForeignKeyOnUpdateModeDefiner
     */
    public function onDeleteSetNull() : ForeignKeyOnUpdateModeDefiner
    {
        return $this->onDelete(ForeignKeyMode::SET_NULL);
    }

    /**
     * Defines the foreign key to throw an error when the referenced rows
     * are deleted.
     *
     * @return ForeignKeyOnUpdateModeDefiner
     */
    public function onDeleteDoNothing() : ForeignKeyOnUpdateModeDefiner
    {
        return $this->onDelete(ForeignKeyMode::DO_NOTHING);
    }

    /**
     * @param string $mode
     *
     * @return ForeignKeyOnUpdateModeDefiner
     */
    protected function onDelete(string $mode) : ForeignKeyOnUpdateModeDefiner
    {
        return new ForeignKeyOnUpdateModeDefiner(
                $this->callback,
                $this->localColumnNames,
                $this->referencedColumnNames,
                $this->referencedTableName,
                $mode
        );
    }
}