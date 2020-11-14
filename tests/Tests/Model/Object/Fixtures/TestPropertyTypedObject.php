<?php

namespace Dms\Core\Tests\Model\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\TypedObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPropertyTypedObject extends TypedObject
{
    public string $string;
    public int $int;
    public float $float;
    public bool $bool;
    public array $array;
    public object $object;
    public iterable $iterable;
    public \ArrayObject $arrayObject;
    public ?string $nullableString;
    public ?\ArrayObject $nullableArrayObject;

    protected function define(ClassDefinition $class)
    {
        
    }
}