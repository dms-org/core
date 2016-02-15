<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Table;

use Dms\Core\Model\IObjectSet;

/**
 * The table definer base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class TableDefinerBase
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * TableDefiner constructor.
     *
     * @param string   $name
     * @param callable $callback
     */
    public function __construct(string $name, callable $callback)
    {
        $this->name     = $name;
        $this->callback = $callback;
    }
}