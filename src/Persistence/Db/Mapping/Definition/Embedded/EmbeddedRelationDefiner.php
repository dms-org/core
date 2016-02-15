<?php declare(strict_types = 1);

namespace Dms\Core\Persistence\Db\Mapping\Definition\Embedded;

use Dms\Core\Persistence\Db\Mapping\IOrm;

/**
 * The embedded relation definer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class EmbeddedRelationDefiner
{
    /**
     * @var IOrm
     */
    protected $orm;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * EmbeddedRelationDefiner constructor.
     *
     * @param IOrm     $orm
     * @param callable $callback
     */
    public function __construct(IOrm $orm, callable $callback)
    {
        $this->orm      = $orm;
        $this->callback = $callback;
    }
}