<?php

namespace Dms\Core\Tests\Form\Binding\Accessor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Binding\Accessor\GetterSetterMethodAccessor;
use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GetterSetterMethodAccessorTest extends FieldAccessorTest
{
    /**
     * @return IField
     */
    protected function buildField()
    {
        return Field::name('abc')->label('Abc')->int()->required()->build();
    }

    /**
     * @return string
     */
    protected function getObjectType()
    {
        return TestFormBoundClass::class;
    }

    /**
     * @param string $objectType
     *
     * @return IFieldAccessor
     */
    protected function buildFieldAccessor($objectType) : IFieldAccessor
    {
        return new GetterSetterMethodAccessor(
                TestFormBoundClass::class,
                'getInt', 'setInt'
        );
    }

    public function testGet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->assertSame(10, $this->binding->getAccessor()->getValueFromObject($object));
    }

    public function testSet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->binding->getAccessor()->bindValueToObject($object, 123);

        $this->assertSame(123, $object->int);
    }

    public function testInvalidGetterMethod()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        return new GetterSetterMethodAccessor(
                $this->buildField()->getName(),
                TestFormBoundClass::class,
                'nonExistentMethod', 'setInt'
        );
    }

    public function testInvalidSetterMethod()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        return new GetterSetterMethodAccessor(
                $this->buildField()->getName(),
                TestFormBoundClass::class,
                'getInt', 'nonExistentMethod'
        );
    }
}