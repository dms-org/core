<?php

namespace Iddigital\Cms\Core\Tests\Form\Processor;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Form\IFormProcessor;
use Iddigital\Cms\Core\Language\Message;

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

    protected function setUp()
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
}