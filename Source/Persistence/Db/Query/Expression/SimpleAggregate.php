<?php

namespace Dms\Core\Persistence\Db\Query\Expression;

use Dms\Core\Persistence\Db\Schema\Type\Decimal;
use Dms\Core\Persistence\Db\Schema\Type\Integer;
use Dms\Core\Persistence\Db\Schema\Type\Type;

/**
 * The aggregate expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleAggregate extends Aggregate
{
    const SUM = 'sum';
    const AVG = 'avg';
    const MAX = 'max';
    const MIN = 'min';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Expr
     */
    protected $argument;

    /**
     * SimpleAggregate constructor.
     *
     * @param string $type
     * @param Expr   $argument
     */
    public function __construct($type, Expr $argument)
    {
        $this->type     = $type;
        $this->argument = $argument;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Expr
     */
    public function getArgument()
    {
        return $this->argument;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {
        return [$this->argument];
    }

    /**
     * Gets the resulting type of the expression
     *
     * @return Type
     */
    public function getResultingType()
    {
        if ($this->type === self::AVG && $this->argument->getResultingType() instanceof Integer) {
            return new Decimal(30, 15);
        }

        return $this->argument->getResultingType();
    }
}