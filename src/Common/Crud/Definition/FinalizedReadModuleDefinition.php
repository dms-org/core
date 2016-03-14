<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Definition;

use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Module\Definition\FinalizedModuleDefinition;

/**
 * The finalized read module definition class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FinalizedReadModuleDefinition extends FinalizedModuleDefinition
{
    /**
     * @var callable
     */
    private $labelObjectCallback;

    /**
     * @inheritDoc
     */
    public function __construct(
        string $name,
        array $metadata,
        callable $labelObjectCallback,
        ISummaryTable $summaryTable,
        array $requiredPermissions,
        array $actions,
        array $tables,
        array $charts,
        array $widgets
    ) {
        parent::__construct($name, $metadata, $requiredPermissions, $actions, array_merge($tables, [$summaryTable]), $charts, $widgets);
        $this->labelObjectCallback = $labelObjectCallback;
    }

    /**
     * @return callable
     */
    public function getLabelObjectCallback() : callable
    {
        return $this->labelObjectCallback;
    }
}