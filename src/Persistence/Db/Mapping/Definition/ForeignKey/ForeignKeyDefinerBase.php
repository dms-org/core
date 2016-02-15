<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\ForeignKey;

/**
 * The foreign key definer base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ForeignKeyDefinerBase
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string[]|null
     */
    protected $localColumnNames;

    /**
     * @var string[]|null
     */
    protected $referencedColumnNames;

    /**
     * @var string|null
     */
    protected $referencedTableName;

    /**
     * @var string|null
     */
    protected $onDeleteMode;

    /**
     * @var string|null
     */
    protected $onUpdateMode;

    /**
     * ForeignKeyDefinerBase constructor.
     *
     * @param callable       $callback
     * @param null|\string[] $localColumnNames
     * @param null|string    $referencedTableName
     * @param null|\string[] $referencedColumnNames
     * @param null|string    $onDeleteMode
     * @param null|string    $onUpdateMode
     */
    public function __construct(
            callable $callback,
            array $localColumnNames = null,
            array$referencedColumnNames = null,
            string $referencedTableName = null,
            string $onDeleteMode = null,
            string $onUpdateMode = null
    ) {
        $this->callback              = $callback;
        $this->localColumnNames      = $localColumnNames;
        $this->referencedColumnNames = $referencedColumnNames;
        $this->referencedTableName   = $referencedTableName;
        $this->onDeleteMode          = $onDeleteMode;
        $this->onUpdateMode          = $onUpdateMode;
    }
}