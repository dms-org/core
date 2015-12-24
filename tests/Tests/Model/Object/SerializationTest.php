<?php

namespace Dms\Core\Tests\Model\Object;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Tests\Model\Object\Fixtures\Person;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SerializationTest extends CmsTestCase
{
    public function testPhpSerialize()
    {
        $person = new Person(
                'Joe',
                'Lowry',
                new \DateTime('1985-02-24'),
                true,
                'joe@gmail.com.au'
        );

        $this->assertEquals($person, unserialize(serialize($person)));
    }
}