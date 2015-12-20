<?php

namespace Dms\Core\Tests\Form\Processor;

use Dms\Core\Form\IFormProcessor;
use Dms\Core\Form\Processor\CustomFormProcessor;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CustomFormProcessorTest extends FormProcessorTest
{

    /**
     * @return IFormProcessor
     */
    protected function processor()
    {
        return new CustomFormProcessor(
                function (array $input) {
                    foreach ($input as &$value) {
                        $value .= 'foo';
                    }

                    return $input;
                },
                function (array $input) {
                    foreach ($input as &$value) {
                        $value = substr($value, 0, -3);
                    }

                    return $input;
                }
        );
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
                [['field' => 'value'], ['field' => 'valuefoo']],
                [['field' => 'value', 'foo' => 'bar'], ['field' => 'valuefoo', 'foo' => 'barfoo']],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
                [['field' => 'valuefoo'], ['field' => 'value']],
                [['field' => 'valuefoo', 'foo' => 'barfoo'], ['field' => 'value', 'foo' => 'bar']],
        ];
    }
}