<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor;

use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\DateTimeProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeProcessorTest extends FieldProcessorTest
{
    /**
     * @return IFieldProcessor
     */
    protected function processor()
    {
        return new DateTimeProcessor('Y-m-d H:i:s');
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(\DateTimeImmutable::class)->nullable();
    }

    /**
     * @return array[]
     */
    public function processTests()
    {
        return [
            [null, null],
            ['2000-01-01 00:00:00', \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00')],
            ['2001-02-03 12:12:11', \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2001-02-03 12:12:11')],
        ];
    }

    /**
     * @return array[]
     */
    public function unprocessTests()
    {
        return [
            [null, null],
            [\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00'), '2000-01-01 00:00:00'],
            [\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2001-02-03 12:12:11'), '2001-02-03 12:12:11'],
        ];
    }

    public function testModeZeroTime()
    {
        $messages = [];
        $processor = new DateTimeProcessor('Y-m-d', null, DateTimeProcessor::MODE_ZERO_TIME);

        $this->assertEquals(new \DateTime('2000-01-02 00:00:00'), $processor->process('2000-01-02', $messages));
    }

    public function testModeZeroDate()
    {
        $messages = [];
        $processor = new DateTimeProcessor('H:i:s', null, DateTimeProcessor::MODE_ZERO_DATE);

        $this->assertEquals(new \DateTime('0000-01-01 12:34:56'), $processor->process('12:34:56', $messages));
    }
}