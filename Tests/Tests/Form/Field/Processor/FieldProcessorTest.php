<?php

namespace Dms\Core\Tests\Form\Field\Processor;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldProcessorTest extends CmsTestCase
{
    /**
     * @var IFieldProcessor
     */
    protected $processor;

    /**
     * @return IFieldProcessor
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
     * @return IType
     */
    abstract protected function processedType();

    protected function setUp()
    {
        $this->processor = $this->processor();
    }

    public function testProcessedType()
    {
        $this->assertEquals($this->processedType(), $this->processor->getProcessedType());
    }

    /**
     * @dataProvider processTests
     */
    public function testProcessesDataCorrectly($input, $output, array $messages = [])
    {
        $actualMessages = [];
        $actualOutput = $this->processor->process($input, $actualMessages);

        $this->assertContainsOnlyInstancesOf(Message::class, $actualMessages);
        $this->assertEquals($output, $actualOutput);
        $this->assertEquals($messages, $actualMessages);
    }

    /**
     * @dataProvider unprocessTests
     */
    public function testUnprocessesDataCorrectly($input, $output)
    {
        $actualOutput = $this->processor->unprocess($input);

        $this->assertEquals($output, $actualOutput);
    }
}