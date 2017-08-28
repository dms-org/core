<?php

namespace Dms\Core\Tests\Form\Binding\Accessor;

use Dms\Core\Form\Binding\Accessor\CustomFieldAccessor;
use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFieldAccessorTest extends FieldAccessorTest
{
    /**
     * @return IField
     */
    protected function buildField()
    {
        return Field::name('abc')->label('Abc')->string()->build();
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
        return new CustomFieldAccessor(
                $objectType,
                function (TestFormBoundClass $object) {
                    return substr($object->string, strlen('prefix:'));
                },
                function (TestFormBoundClass $object, $input) {
                    $object->string = 'prefix:' . $input;
                }
        );
    }

    public function testGet()
    {
        $object = new TestFormBoundClass('prefix:abc', 10, false);

        $this->assertSame('abc', $this->binding->getAccessor()->getValueFromObject($object));
    }

    public function testSet()
    {
        $object = new TestFormBoundClass('prefix:abc', 10, false);

        $this->binding->getAccessor()->bindValueToObject($object, 'foobar');

        $this->assertSame('prefix:foobar', $object->string);
    }
}