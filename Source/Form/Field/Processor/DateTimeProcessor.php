<?php

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\Type\ObjectType;

/**
 * The field type processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeProcessor extends FieldProcessor
{
    const MODE_ZERO_TIME = 'zero-time';
    const MODE_ZERO_DATE = 'zero-date';

    /**
     * @var string
     */
    private $format;

    /**
     * @var \DateTimeZone
     */
    private $timeZone;

    /**
     * @var string|null
     */
    private $mode;

    /**
     * @param string             $format
     * @param \DateTimeZone|null $timeZone
     * @param string|null        $mode
     */
    public function __construct($format, \DateTimeZone $timeZone = null, $mode = null)
    {
        parent::__construct(new ObjectType(\DateTimeImmutable::class));

        $this->format   = $format;
        $this->timeZone = $timeZone;
        $this->mode     = $mode;
    }

    protected function doProcess($input, array &$messages)
    {
        $format = '!' . $this->format;

        if ($this->timeZone) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $input, $this->timeZone);
        } else {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $input);
        }

        $dateTime = self::zeroUnusedParts($this->mode, $dateTime);

        return $dateTime;
    }

    protected function doUnprocess($input)
    {
        /** @var \DateTime $input */
        return $input->format($this->format);
    }

    /**
     * Zeros the unused parts of the date time according to the processor
     * mode.
     *
     * @param string|null        $mode
     * @param \DateTimeImmutable $dateTime
     *
     * @return \DateTimeImmutable
     */
    public static function zeroUnusedParts($mode, \DateTimeImmutable $dateTime)
    {
        if ($mode === self::MODE_ZERO_TIME) {
            $dateTime = $dateTime->setTime(0, 0, 0);
        } elseif ($mode === self::MODE_ZERO_DATE) {
            $dateTime = $dateTime->setDate(0, 1, 1);
        }

        return $dateTime;
    }
}