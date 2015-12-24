<?php

namespace Dms\Core\Tests\Form\Binding\Field;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Binding\IFieldBinding;
use Dms\Core\Form\IField;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldBindingTest extends CmsTestCase
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
     * @param IField $field
     * @param string $objectType
     *
     * @return IFieldBinding
     */
    abstract protected function buildFormBinding(IField $field, $objectType);

    public function setUp()
    {
        $this->field      = $this->buildField();
        $this->objectType = $this->getObjectType();
        $this->binding    = $this->buildFormBinding($this->field, $this->objectType);
    }

    public function testGetters()
    {
        $this->assertSame($this->field->getName(), $this->binding->getFieldName());
        $this->assertSame($this->objectType, $this->binding->getObjectType());
    }

    public function testInvalidObjectInGetter()
    {
        $this->assertThrows(function () {
            $this->binding->getFieldValueFromObject(new \stdClass());
        }, TypeMismatchException::class);

        $this->assertThrows(function () {
            $this->binding->getFieldValueFromObject(null);
        }, TypeMismatchException::class);
    }

    public function testInvalidObjectInSetter()
    {
        $this->assertThrows(function () {
            $this->binding->bindFieldValueToObject(new \stdClass(), null);
        }, TypeMismatchException::class);

        $this->assertThrows(function () {
            $this->binding->bindFieldValueToObject(null, null);
        }, TypeMismatchException::class);
    }
}