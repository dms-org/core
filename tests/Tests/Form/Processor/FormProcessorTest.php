<?php

namespace Dms\Core\Tests\Form\Processor;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\IFormProcessor;
use Dms\Core\Form\Processor\ArrayKeyHelper;
use Dms\Core\Language\Message;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FormProcessorTest extends CmsTestCase
{
    /**
     * @var IFormProcessor
     */
    protected $processor;

    /**
     * @return IFormProcessor
     */
    abstract protected function processor();

    /**
     * @return array[]
     */
    abstract public function processTests();

    /**
     * @return array[]
     */
    abstract public function unprocessTests();

    /**
     * @return string[]
     */
    abstract public function fieldNameMap();

    protected function setUp(): void
    {
        $this->processor = $this->processor();
    }

    /**
     * @dataProvider processTests
     */
    public function testProcessesDataCorrectly(array $input, array $output, array $messages = [])
    {
        $actualMessages = [];
        $actualOutput   = $this->processor->process($input, $actualMessages);

        $this->assertContainsOnlyInstancesOf(Message::class, $actualMessages);
        $this->assertEquals($output, $actualOutput);
        $this->assertEquals($messages, $actualMessages);
    }

    /**
     * @dataProvider unprocessTests
     */
    public function testUnprocessesDataCorrectly(array $input, array $output)
    {
        $actualOutput = $this->processor->unprocess($input);

        $this->assertEquals($output, $actualOutput);
    }

    /**
     * @dataProvider processTests
     */
    public function testProcessesDataCorrectlyWithFieldNameMap(array $input, array $output, array $messages = [])
    {
        $fieldNameMap    = $this->fieldNameMap();
        $this->processor = $this->processor->withFieldNames($fieldNameMap);

        $input  = ArrayKeyHelper::mapArrayKeys($input, $fieldNameMap);
        $output = ArrayKeyHelper::mapArrayKeys($output, $fieldNameMap);

        $this->testProcessesDataCorrectly($input, $output, $messages);
    }

    /**
     * @dataProvider unprocessTests
     */
    public function testUnprocessesDataCorrectlyWithFieldNameMap(array $input, array $output)
    {
        $fieldNameMap    = $this->fieldNameMap();
        $this->processor = $this->processor->withFieldNames($fieldNameMap);

        $input  = ArrayKeyHelper::mapArrayKeys($input, $fieldNameMap);
        $output = ArrayKeyHelper::mapArrayKeys($output, $fieldNameMap);

        $this->testUnprocessesDataCorrectly($input, $output);
    }
}