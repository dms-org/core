<?php

namespace Dms\Core\Tests\Common\Crud\Modules\Fixtures\ValueObject;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\ValueObject;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SimpleValueObject extends ValueObject
{
    const DATA = 'data';

    /**
     * @var string
     */
    public $data;

    /**
     * @inheritDoc
     */
    public function __construct(string $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->data)->asString();
    }
}