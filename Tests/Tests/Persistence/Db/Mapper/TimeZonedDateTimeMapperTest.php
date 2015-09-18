<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\TimeZonedDateTime;
use Iddigital\Cms\Core\Persistence\Db\Mapper\TimeZonedDateTimeMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeZonedDateTimeMapperTest extends ValueObjectMapperTest
{
    /**
     * @return IEmbeddedObjectMapper
     */
    protected function buildMapper()
    {
        return new TimeZonedDateTimeMapper();
    }

    /**
     * @return array[]
     */
    public function mapperTests()
    {
        return [
                [
                        ['datetime' => new \DateTimeImmutable('1970-01-01 00:00:00', new \DateTimeZone('UTC')), 'timezone' => 'UTC'],
                        TimeZonedDateTime::fromString('1970-01-01 00:00:00', 'UTC')
                ],
                [
                        ['datetime' => new \DateTimeImmutable('1997-05-04 12:13:14', new \DateTimeZone('UTC')), 'timezone' => 'Australia/Melbourne'],
                        TimeZonedDateTime::fromString('1997-05-04 12:13:14', 'Australia/Melbourne')
                ],
                [
                        ['datetime' => new \DateTimeImmutable('2022-10-12 00:00:01', new \DateTimeZone('UTC')), 'timezone' => 'Europe/Berlin'],
                        TimeZonedDateTime::fromString('2022-10-12 00:00:01', 'Europe/Berlin')
                ],
                [
                        ['datetime' => new \DateTimeImmutable('1975-4-6 00:05:00', new \DateTimeZone('UTC')), 'timezone' => 'America/New_York'],
                        TimeZonedDateTime::fromString('1975-4-6 00:05:00', 'America/New_York')
                ],
        ];
    }
}