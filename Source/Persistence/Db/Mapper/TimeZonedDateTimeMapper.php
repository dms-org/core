<?php

namespace Iddigital\Cms\Core\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\TimeZonedDateTime;
use Iddigital\Cms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;

/**
 * The timezoned date time value object mapper
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeZonedDateTimeMapper extends SimpleValueObjectMapper
{
    /**
     * @var string
     */
    private $dateTimeColumnName;

    /**
     * @var string
     */
    private $timezoneColumnName;

    public function __construct($dateTimeColumnName = 'datetime', $timezoneColumnName = 'timezone')
    {
        $this->dateTimeColumnName = $dateTimeColumnName;
        $this->timezoneColumnName = $timezoneColumnName;
        parent::__construct();
    }

    /**
     * Defines the value object mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(TimeZonedDateTime::class);

        $map->property('dateTime')
                ->mappedVia(function (\DateTimeImmutable $phpDateTime) {
                    // Remove timezone information as this is lost when persisted anyway
                    // The timezone will be stored in a separate column
                    return \DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            $phpDateTime->format('Y-m-d H:i:s'),
                            new \DateTimeZone('UTC')
                    );
                }, function (\DateTimeImmutable $dbDateTime, array $row) {
                    // When persisted, the date time instance will lose its timezone information
                    // so it is loaded as if it is UTC but the actual timezone is stored in a
                    // separate column. So we can create a new date time in the correct timezone
                    // from the string representation of it in that timezone.
                    return \DateTimeImmutable::createFromFormat(
                            'Y-m-d H:i:s',
                            $dbDateTime->format('Y-m-d H:i:s'),
                            new \DateTimeZone($row['timezone'])
                    );
                })
                ->to($this->dateTimeColumnName)
                ->asDateTime();

        $map->computed(
                function (TimeZonedDateTime $dateTime) {
                    return $dateTime->getTimezone()->getName();
                })
                ->to($this->timezoneColumnName)
                ->asVarchar(50);
    }
}