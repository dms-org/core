<?php

namespace Iddigital\Cms\Core\Tests\Model;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\Model\Object\PropertyAccessibility;
use Iddigital\Cms\Core\Tests\Model\Object\TypedObjectTest;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PropertyAccessibilityTest extends CmsTestCase
{
    public $public;
    protected $protected;
    private $private;

    public function testFromReflectionPublic()
    {
        $accessibility = PropertyAccessibility::from(new \ReflectionProperty(__CLASS__, 'public'));

        $this->assertSame(PropertyAccessibility::ACCESS_PUBLIC, $accessibility->getAccessibility());
        $this->assertSame(__CLASS__, $accessibility->getDeclaredClass());
        $this->assertTrue($accessibility->isPublic());
    }

    public function testFromReflectionProtected()
    {
        $accessibility = PropertyAccessibility::from(new \ReflectionProperty(__CLASS__, 'protected'));

        $this->assertSame(PropertyAccessibility::ACCESS_PROTECTED, $accessibility->getAccessibility());
        $this->assertSame(__CLASS__, $accessibility->getDeclaredClass());
        $this->assertFalse($accessibility->isPublic());
    }

    public function testFromReflectionPrivate()
    {
        $accessibility = PropertyAccessibility::from(new \ReflectionProperty(__CLASS__, 'private'));

        $this->assertSame(PropertyAccessibility::ACCESS_PRIVATE, $accessibility->getAccessibility());
        $this->assertSame(__CLASS__, $accessibility->getDeclaredClass());
        $this->assertFalse($accessibility->isPublic());
    }

    public function testIsAccessiblePublic()
    {
        $accessibility = new PropertyAccessibility(PropertyAccessibility::ACCESS_PUBLIC, \stdClass::class);

        $this->assertTrue($accessibility->isAccessibleFrom(\DateTime::class));
        $this->assertTrue($accessibility->isAccessibleFrom(\stdClass::class));
        $this->assertTrue($accessibility->isAccessibleFrom(null));
    }

    public function testIsAccessibleProtected()
    {
        $accessibility = new PropertyAccessibility(PropertyAccessibility::ACCESS_PROTECTED, CmsTestCase::class);

        $this->assertTrue($accessibility->isAccessibleFrom(__CLASS__));
        $this->assertTrue($accessibility->isAccessibleFrom(TypedObjectTest::class));

        $this->assertFalse($accessibility->isAccessibleFrom(\DateTime::class));
        $this->assertFalse($accessibility->isAccessibleFrom(\stdClass::class));
        $this->assertFalse($accessibility->isAccessibleFrom(null));
    }

    public function testIsAccessiblePrivate()
    {
        $accessibility = new PropertyAccessibility(PropertyAccessibility::ACCESS_PRIVATE, \DateTime::class);

        $this->assertTrue($accessibility->isAccessibleFrom(\DateTime::class));

        $this->assertFalse($accessibility->isAccessibleFrom(\stdClass::class));
        $this->assertFalse($accessibility->isAccessibleFrom(\DateTimeImmutable::class));
        $this->assertFalse($accessibility->isAccessibleFrom(null));
    }
}