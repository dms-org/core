<?php

namespace Dms\Core\Tests\Form\Binding\Accessor;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\Binding\FieldBinding;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\IField;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldAccessorTest extends CmsTestCase
{
    /**
     * @var IField
     */
    protected $field;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var IFieldBinding
     */
    protected $binding;

    /**
     * @return IField
     */
    abstract protected function buildField();

    /**
     * @return string
     */
    abstract protected function getObjectType();


    /**
     * @param string $objectType
     *
     * @return IFieldAccessor
     */
    abstract protected function buildFieldAccessor($objectType) : IFieldAccessor;

    public function setUp(): void
    {
        $this->field      = $this->buildField();
        $this->objectType = $this->getObjectType();
        $this->binding    = new FieldBinding($this->field->getName(), $this->buildFieldAccessor($this->objectType));
    }

    public function testGetters()
    {
        $this->assertSame($this->field->getName(), $this->binding->getFieldName());
        $this->assertSame($this->objectType, $this->binding->getObjectType());
    }

    public function testInvalidObjectInGetter()
    {
        $this->assertThrows(function () {
            $this->binding->getAccessor()->getValueFromObject(new \stdClass());
        }, TypeMismatchException::class);

        $this->assertThrows(function () {
            $this->binding->getAccessor()->getValueFromObject(null);
        }, TypeMismatchException::class);
    }

    public function testInvalidObjectInSetter()
    {
        $this->assertThrows(function () {
            $this->binding->getAccessor()->bindValueToObject(new \stdClass(), null);
        }, TypeMismatchException::class);

        $this->assertThrows(function () {
            $this->binding->getAccessor()->bindValueToObject(null, null);
        }, TypeMismatchException::class);
    }
}