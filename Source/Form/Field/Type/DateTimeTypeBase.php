<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Processor\DateTimeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Iddigital\Cms\Core\Model\Type\Builder\Type as PhpType;

/**
 * The date time type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DateTimeTypeBase extends FieldType
{
    const ATTR_FORMAT = 'format';
    const ATTR_TIMEZONE = 'timezone';
    const ATTR_MIN = 'min';
    const ATTR_MAX = 'max';

    /**
     * @var string
     */
    private $mode;

    /**
     * @var \DateInterval
     */
    private $unit;

    public function __construct($format, \DateTimeZone $timeZone = null, $mode, \DateInterval $unit)
    {
        $this->attributes[self::ATTR_FORMAT]   = $format;
        $this->attributes[self::ATTR_TIMEZONE] = $timeZone;
        $this->unit                            = $unit;
        $this->mode                            = $mode;
        parent::__construct();
    }

    /**
     * Gets the processor mode
     *
     * @return string|null
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Gets the date unit interval
     *
     * @return \DateInterval
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->get(self::ATTR_FORMAT);
    }

    /**
     * @return \DateTimeZone|null
     */
    public function getTimezone()
    {
        return $this->get(self::ATTR_TIMEZONE);
    }

    /**
     * {@inheritdoc}
     */
    public function buildPhpTypeOfInput()
    {
        return PhpType::string();
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors()
    {
        return [
                new DateFormatValidator($this->inputType, $this->getFormat()),
                new DateTimeProcessor($this->getFormat(), $this->getTimezone(), $this->mode),
        ];
    }
}