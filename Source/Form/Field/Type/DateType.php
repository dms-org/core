<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\DateTimeProcessor;

/**
 * The date type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateType extends DateTimeTypeBase
{
    public function __construct($format, \DateTimeZone $timeZone = null)
    {
        parent::__construct($format, $timeZone, DateTimeProcessor::MODE_ZERO_TIME, new \DateInterval('P1D'));
    }
}