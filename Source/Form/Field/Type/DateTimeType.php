<?php

namespace Dms\Core\Form\Field\Type;

/**
 * The date time type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeType extends DateTimeTypeBase
{
    public function __construct($format, \DateTimeZone $timeZone = null)
    {
        parent::__construct($format, $timeZone, null, new \DateInterval('PT1S'));
    }
}