<?php

namespace Dms\Core\Tests\Model\Object\Proxy\Fixtures;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class AbstractMethods
{
    final protected function __construct($required) {}

    protected function normalMethod()
    {

    }

    public function anotherNormalMethod()
    {

    }

    abstract protected function abstractMethod();

    abstract public function abstractPublicMethod();

    abstract public function abstractMethodWithParams($a, $b, $c);

    abstract public function abstractMethodWithComplexParams(array &$a = null, callable $b = null, self $c, $foo = \PDO::ATTR_AUTOCOMMIT);

    abstract protected function abstractMethodWithMoreComplexParams(\DateTime &$foo = null, AbstractMethods &$self);
}