<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\DateTime;
use Iddigital\Cms\Core\Persistence\Db\Mapper\DateTimeMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateTimeMapperTest extends ValueObjectMapperTest
{
    /**
     * @return IEmbeddedObjectMapper
     */
    protected function buildMapper()
    {
        return new DateTimeMapper('datetime');
    }

    /**
     * @return array[]
     */
    public function mapperTests()
    {
        return [
                [['datetime' => new \DateTimeImmutable('1970-01-01 00:00:00')], DateTime::fromString('1970-01-01 00:00:00')],
                [['datetime' => new \DateTimeImmutable('1997-05-04 12:13:14')], DateTime::fromString('1997-05-04 12:13:14')],
                [['datetime' => new \DateTimeImmutable('2042-10-12 00:00:01')], DateTime::fromString('2042-10-12 00:00:01')],
                [['datetime' => new \DateTimeImmutable('0002-4-6 00:05:00')], DateTime::fromString('0002-4-6 00:05:00')],
        ];
    }
}