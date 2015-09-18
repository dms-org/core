<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\Time;
use Iddigital\Cms\Core\Persistence\Db\Mapper\TimeMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TimeMapperTest extends ValueObjectMapperTest
{
    /**
     * @return IEmbeddedObjectMapper
     */
    protected function buildMapper()
    {
        return new TimeMapper('time');
    }

    /**
     * @return array[]
     */
    public function mapperTests()
    {
        return [
                [['time' => new \DateTimeImmutable('1970-01-01 12:00:00')], new Time(12, 0, 0)],
                [['time' => new \DateTimeImmutable('1970-01-01 11:11:11')], new Time(11, 11, 11)],
                [['time' => new \DateTimeImmutable('1970-01-01 23:59:59')], new Time(23, 59, 59)],
        ];
    }
}