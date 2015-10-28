<?php

namespace Iddigital\Cms\Core\Persistence\Db\Query\Expression;

/**
 * The aggregate expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ArgumentAggregate extends Aggregate
{
    /**
     * @var Expr
     */
    protected $argument;

    /**
     * ExpressionAggregate constructor.
     *
     * @param Expr $argument
     */
    public function __construct(Expr $argument)
    {
        $this->argument = $argument;
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
}