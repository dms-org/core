<?php

namespace Dms\Core\Util;

/**
 * The reflection helper class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Reflection
{
    /**
     * Gets a reflection from the supplied callable.
     *
     * @param callable $callable
     *
     * @return \ReflectionFunctionAbstract
     * @throws \Pinq\Parsing\InvalidFunctionException
     */
    public static function fromCallable(callable $callable)
    {
        return \Pinq\Parsing\Reflection::fromCallable($callable);
    }
}