<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\IType;

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