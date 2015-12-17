<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\InvalidEnumValueException;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\InvalidTypeEnum;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\TestEnum;
use Iddigital\Cms\Core\Tests\Model\Object\Fixtures\TestSubclassEnum;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EnumTest extends CmsTestCase
{
    public function testInvalidType()
    {
        $this->setExpectedException(InvalidEnumValueException::class);

        InvalidTypeEnum::getOptions();
    }

    public function testGetOptions()
    {
        $this->assertEquals(['ONE' => 'one', 'TWO' => 'two', 'THREE' => 'three'], TestEnum::getOptions());

        $this->assertEquals([
                'ONE' => 'one', 'TWO' => 'two', 'THREE' => 'three',
                'FOUR' => 'four', 'FIVE' => 'five', 'SIX' => 'six',
        ], TestSubclassEnum::getOptions());
    }

    public function testGetEnumType()
    {
        $this->assertSame('string', TestEnum::getEnumType()->asTypeString());
        $this->assertSame('string', TestSubclassEnum::getEnumType()->asTypeString());
    }

    public function testIsValue()
    {
        $this->assertTrue(TestEnum::isValid('one'));
        $this->assertTrue(TestEnum::isValid('two'));
        $this->assertTrue(TestEnum::isValid('three'));

        $this->assertFalse(TestEnum::isValid('four'));
        $this->assertFalse(TestEnum::isValid(null));
        $this->assertFalse(TestEnum::isValid(1));

        $this->assertTrue(TestSubclassEnum::isValid('one'));
        $this->assertTrue(TestSubclassEnum::isValid('two'));
        $this->assertTrue(TestSubclassEnum::isValid('three'));
        $this->assertTrue(TestSubclassEnum::isValid('four'));
        $this->assertTrue(TestSubclassEnum::isValid('five'));
        $this->assertTrue(TestSubclassEnum::isValid('six'));

        $this->assertFalse(TestSubclassEnum::isValid('seven'));
        $this->assertFalse(TestSubclassEnum::isValid(null));
        $this->assertFalse(TestSubclassEnum::isValid(1));
    }

    public function testNewEnum()
    {
        $this->assertSame('one', (new TestEnum(TestEnum::ONE))->getValue());

        $this->assertSame('one', (new TestSubclassEnum(TestSubclassEnum::ONE))->getValue());
    }

    public function testGetAll()
    {
        $this->assertEquals([
                new TestEnum(TestEnum::ONE),
                new TestEnum(TestEnum::TWO),
                new TestEnum(TestEnum::THREE),
        ], TestEnum::getAll());
    }

    public function testIs()
    {
        $enum = new TestEnum(TestEnum::ONE);

        $this->assertTrue($enum->is($enum));
        $this->assertTrue($enum->is(new TestEnum(TestEnum::ONE)));
        $this->assertTrue($enum->is($enum->getValue()));
        $this->assertTrue($enum->is(TestEnum::ONE));

        $this->assertFalse($enum->is(new \stdClass()));
        $this->assertFalse($enum->is(null));
        $this->assertFalse($enum->is(new TestEnum(TestEnum::TWO)));
        $this->assertFalse($enum->is(TestEnum::TWO));
        $this->assertFalse($enum->is(new TestSubclassEnum(TestSubclassEnum::ONE)));
        $this->assertFalse($enum->is(new TestSubclassEnum(TestSubclassEnum::TWO)));


        $this->assertTrue((new TestSubclassEnum(TestSubclassEnum::FIVE))->is(TestSubclassEnum::FIVE));

        $this->assertFalse((new TestSubclassEnum(TestSubclassEnum::ONE))->is($enum));
    }

    public function testThrowsOnInvalidValue()
    {
        $this->setExpectedException(InvalidEnumValueException::class);
        new TestEnum('four');
    }

    public function testSerialization()
    {
        $enum = new TestEnum(TestEnum::ONE);

        $this->assertEquals($enum, unserialize(serialize($enum)));

        $enum = new TestEnum(TestEnum::THREE);

        $this->assertEquals($enum, unserialize(serialize($enum)));
    }

    public function testObjectHash()
    {
        $one = new TestEnum(TestEnum::ONE);
        $two = new TestEnum(TestEnum::TWO);
        $three = new TestEnum(TestEnum::THREE);

        $this->assertSame($one->getObjectHash(), $one->getObjectHash());
        $this->assertSame($two->getObjectHash(), $two->getObjectHash());
        $this->assertSame($three->getObjectHash(), $three->getObjectHash());

        $this->assertNotEquals($one->getObjectHash(), $two->getObjectHash());
        $this->assertNotEquals($one->getObjectHash(), $three->getObjectHash());
        $this->assertNotEquals($two->getObjectHash(), $three->getObjectHash());
    }
}