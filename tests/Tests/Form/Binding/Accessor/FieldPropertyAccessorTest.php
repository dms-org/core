<?php

namespace Dms\Core\Tests\Form\Binding\Accessor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Binding\Accessor\FieldPropertyAccessor;
use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldPropertyAccessorTest extends FieldAccessorTest
{
    /**
     * @return IField
     */
    protected function buildField()
    {
        return Field::name('abc')->label('Abc')->string()->required()->build();
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
    protected function buildFieldAccessor($objectType): IFieldAccessor
    {
        return new FieldPropertyAccessor(
            TestFormBoundClass::definition(),
            'string'
        );
    }

    public function testGet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->assertSame('abc', $this->binding->getAccessor()->getValueFromObject($object));
    }

    public function testSet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->binding->getAccessor()->bindValueToObject($object, 'foobar');

        $this->assertSame('foobar', $object->string);
    }

    public function testInvalidProperty()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new FieldPropertyAccessor(
            TestFormBoundClass::definition(),
            'invalid-property-name'
        );
    }
}