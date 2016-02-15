<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\DateTimeProcessor;

/**
 * The time type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeOfDayType extends DateTimeTypeBase
{
    public function __construct($format, \DateTimeZone $timeZone = null)
    {
        parent::__construct($format, $timeZone, DateTimeProcessor::MODE_ZERO_DATE, new \DateInterval('PT1S'));
    }
}