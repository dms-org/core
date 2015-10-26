<?php

namespace Iddigital\Cms\Core\Tests\Form\Binding\Field;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Binding\Field\FieldPropertyBinding;
use Iddigital\Cms\Core\Form\Binding\IFieldBinding;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Tests\Form\Binding\Fixtures\TestFormBoundClass;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldPropertyBindingTest extends FieldBindingTest
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
     * @param IField $field
     * @param string $objectType
     *
     * @return IFieldBinding
     */
    protected function buildFormBinding(IField $field, $objectType)
    {
        return new FieldPropertyBinding(
                $field->getName(),
                TestFormBoundClass::definition(),
                'string'
        );
    }

    public function testGet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->assertSame('abc', $this->binding->getFieldValueFromObject($object));
    }

    public function testSet()
    {
        $object = new TestFormBoundClass('abc', 10, false);

        $this->binding->bindFieldValueToObject($object, 'foobar');

        $this->assertSame('foobar', $object->string);
    }

    public function testInvalidProperty()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        new FieldPropertyBinding(
                $this->buildField(),
                TestFormBoundClass::definition(),
                'invalid-property-name'
        );
    }
}