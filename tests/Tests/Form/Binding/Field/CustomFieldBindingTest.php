<?php

namespace Dms\Core\Tests\Form\Binding\Field;

use Dms\Core\Form\Binding\Field\CustomFieldBinding;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\IField;
use Dms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFieldBindingTest extends FieldBindingTest
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
     * @param IField $field
     * @param string $objectType
     *
     * @return IFieldBinding
     */
    protected function buildFormBinding(IField $field, $objectType)
    {
        return new CustomFieldBinding(
                $field->getName(),
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

        $this->assertSame('abc', $this->binding->getFieldValueFromObject($object));
    }

    public function testSet()
    {
        $object = new TestFormBoundClass('prefix:abc', 10, false);

        $this->binding->bindFieldValueToObject($object, 'foobar');

        $this->assertSame('prefix:foobar', $object->string);
    }
}