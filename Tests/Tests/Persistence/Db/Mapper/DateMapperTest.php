<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Mapper;

use Iddigital\Cms\Core\Model\Object\Type\Date;
use Iddigital\Cms\Core\Persistence\Db\Mapper\DateMapper;
use Iddigital\Cms\Core\Persistence\Db\Mapping\IEmbeddedObjectMapper;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateMapperTest extends ValueObjectMapperTest
{
    /**
     * @return IEmbeddedObjectMapper
     */
    protected function buildMapper()
    {
        return new DateMapper('date');
    }

    /**
     * @return array[]
     */
    public function mapperTests()
    {
        return [
                [['date' => new \DateTimeImmutable('1970-01-01 00:00:00')], new Date(1970, 1, 1)],
                [['date' => new \DateTimeImmutable('1997-05-04 00:00:00')], new Date(1997, 5, 4)],
                [['date' => new \DateTimeImmutable('2042-10-12 00:00:00')], new Date(2042, 10, 12)],
                [['date' => new \DateTimeImmutable('0002-4-6 00:00:00')], new Date(2, 4, 6)],
        ];
    }
}