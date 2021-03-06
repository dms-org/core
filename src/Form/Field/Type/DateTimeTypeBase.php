<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\DateTimeProcessor;
use Dms\Core\Form\Field\Processor\EmptyStringToNullProcessor;
use Dms\Core\Form\Field\Processor\Validator\DateFormatValidator;
use Dms\Core\Model\Type\Builder\Type as PhpType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The date time type base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class DateTimeTypeBase extends FieldType implements IComparableFieldConstants
{
    use ComparableFieldTypeTrait;

    const ATTR_FORMAT = 'format';
    const ATTR_TIMEZONE = 'timezone';

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
    public function getFormat() : string
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
    public function buildPhpTypeOfInput() : IType
    {
        return PhpType::string();
    }

    /**
     * @inheritDoc
     */
    protected function getComparisonType() : IType
    {
        return Type::object(\DateTimeImmutable::class)->nullable();
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors() : array
    {
        return array_merge(
            [
                new EmptyStringToNullProcessor(Type::string()),
                new DateFormatValidator($this->inputType, $this->getFormat()),
                new DateTimeProcessor($this->getFormat(), $this->getTimezone(), $this->mode),
            ],
            $this->buildComparisonProcessors()
        );
    }
}