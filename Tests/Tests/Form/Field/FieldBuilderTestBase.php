<?php

namespace Iddigital\Cms\Core\Tests\Form\Field;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\Field\Type\ScalarType;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\InvalidInputException;
use Iddigital\Cms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldBuilderTestBase extends CmsTestCase
{
    /**
     * @param string $scalarType
     * @param IField $field
     *
     * @return ScalarType
     */
    protected function assertScalarType($scalarType, IField $field)
    {
        $this->assertInstanceOf(ScalarType::class, $field->getType());
        /** @var ScalarType $type */
        $type = $field->getType();
        $this->assertSame($scalarType, $type->getType());

        return $type;
    }

    /**
     * @param array  $attributes
     * @param IField $field
     */
    protected function assertAttributes(array $attributes, IField $field)
    {
        $this->assertEquals($attributes, $field->getType()->attrs());
    }

    /**
     * @param IFieldProcessor $processor
     * @param IField          $field
     */
    protected function assertHasProcessor(IFieldProcessor $processor, IField $field)
    {
        $this->assertContains($processor, $field->getProcessors(), '', false, false); // ==
    }

    /**
     * @param IField    $field
     * @param mixed     $input
     * @param Message[] $messages
     *
     * @return void
     */
    protected function assertFieldThrows(IField $field, $input, array $messages = null)
    {
        /** @var InvalidInputException $exception */
        $exception = $this->assertThrows(function () use ($field, $input) {
            $field->process($input);
        }, InvalidInputException::class);

        $this->assertSame($field, $exception->getField());

        if ($messages !== null) {
            $this->assertEquals($messages, $exception->getMessages());
        }
    }

    /**
     * @param array  $values
     * @param IField $field
     */
    protected function assertProcesses(array $values, IField $field)
    {
        foreach ($values as $value) {
            $field->process($value);
        }
    }

    /**
     * @param array  $values
     * @param IField $field
     */
    protected function assertFailsToProcess(array $values, IField $field)
    {
        foreach ($values as $value) {
            $this->assertThrows(function () use ($field, $value) {
                $field->process($value);
            }, InvalidInputException::class, $value);
        }
    }
}