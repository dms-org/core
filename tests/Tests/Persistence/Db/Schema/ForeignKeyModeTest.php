<?php

namespace Dms\Core\Tests\Persistence\Db\Schema;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Persistence\Db\Schema\ForeignKeyMode;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ForeignKeyModeTest extends CmsTestCase
{
    public function testIsValid()
    {
        $this->assertTrue(ForeignKeyMode::isValid(ForeignKeyMode::CASCADE));
        $this->assertTrue(ForeignKeyMode::isValid(ForeignKeyMode::SET_NULL));
        $this->assertTrue(ForeignKeyMode::isValid(ForeignKeyMode::DO_NOTHING));

        $this->assertFalse(ForeignKeyMode::isValid(null));
        $this->assertFalse(ForeignKeyMode::isValid('foobar'));
    }

    public function testValidate()
    {
        ForeignKeyMode::validate(ForeignKeyMode::CASCADE);
        ForeignKeyMode::validate(ForeignKeyMode::SET_NULL);
        ForeignKeyMode::validate(ForeignKeyMode::DO_NOTHING);

        $this->assertThrows(function () {
            ForeignKeyMode::validate(null);
        }, InvalidArgumentException::class);

        $this->assertThrows(function () {
            ForeignKeyMode::validate('foobar');
        }, InvalidArgumentException::class);
    }
}