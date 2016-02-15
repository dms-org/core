<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyOnUpdateModeDefiner extends ForeignKeyDefinerBase
{
    /**
     * Defines the foreign key to update the keys when the referenced
     * keys are updated.
     *
     * @return void
     */
    public function onUpdateCascade()
    {
        $this->onUpdate(ForeignKeyMode::CASCADE);
    }

    /**
     * Defines the foreign key to set the columns to null
     * when the referenced rows are updated.
     *
     * @return void
     */
    public function onUpdateSetNull()
    {
        $this->onUpdate(ForeignKeyMode::SET_NULL);
    }

    /**
     * Defines the foreign key to throw an error when the referenced keys
     * are updated.
     *
     * @return void
     */
    public function onUpdateDoNothing()
    {
        $this->onUpdate(ForeignKeyMode::DO_NOTHING);
    }

    /**
     * @param string $mode
     *
     * @return void
     */
    protected function onUpdate(string $mode)
    {
        call_user_func($this->callback,
                $this->localColumnNames,
                $this->referencedTableName,
                $this->referencedColumnNames,
                $this->onDeleteMode,
                $mode
        );
    }
}