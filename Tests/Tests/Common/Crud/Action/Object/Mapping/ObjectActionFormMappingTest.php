<?php

namespace Iddigital\Cms\Core\Tests\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Tests\Module\Mapping\StagedFormDtoMappingTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectActionFormMappingTest extends StagedFormDtoMappingTest
{
    /**
     * @var ObjectActionFormMapping
     */
    protected $mapping;

    /**
     * @inheritdoc
     */
    protected function mapping()
    {
        return $this->objectFormMapping();
    }

    /**
     * @return ObjectActionFormMapping
     */
    abstract protected function objectFormMapping();

    /**
     * @return string
     */
    final protected function expectedDtoType()
    {
        return ObjectActionParameter::class;
    }

    /**
     * @return string
     */
    abstract protected function expectedDataDtoType();

    public function testDataDtoType()
    {
        $this->assertSame($this->expectedDataDtoType(), $this->mapping->getDataDtoType());
    }
}