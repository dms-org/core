<?php

namespace Iddigital\Cms\Core\Tests\Form\Type;

use Iddigital\Cms\Common\Testing\CmsTestCase;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Type\ArrayOfType;
use Iddigital\Cms\Core\Form\Field\Type\BoolType;
use Iddigital\Cms\Core\Form\Field\Type\DateTimeType;
use Iddigital\Cms\Core\Form\Field\Type\DateType;
use Iddigital\Cms\Core\Form\Field\Type\FileType;
use Iddigital\Cms\Core\Form\Field\Type\FloatType;
use Iddigital\Cms\Core\Form\Field\Type\ImageType;
use Iddigital\Cms\Core\Form\Field\Type\IntType;
use Iddigital\Cms\Core\Form\Field\Type\StringType;
use Iddigital\Cms\Core\Form\Field\Type\TimeType;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypesTest extends CmsTestCase
{
    public function testInt()
    {
        $type = new IntType();

        $this->assertSame('mixed', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testRequiredAttribute()
    {
        $type = (new IntType())->with(IntType::ATTR_REQUIRED, true);

        $this->assertSame('mixed', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testString()
    {
        $type = new StringType();

        $this->assertSame('mixed', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testBool()
    {
        $type = new BoolType();

        $this->assertSame('mixed', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testFloat()
    {
        $type = new FloatType();

        $this->assertSame('mixed', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testFile()
    {
        $type = new FileType();

        $this->assertSame(IUploadedFile::class . '|null', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testImage()
    {
        $type = new ImageType();

        $this->assertSame(IUploadedImage::class . '|null', $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testTime()
    {
        $type = new TimeType('H:i');

        $this->assertSame("string|null", $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testDate()
    {
        $type = new DateType('Y-m');

        $this->assertSame("string|null", $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testDateTime()
    {
        $type = new DateTimeType('Y-m h:i');

        $this->assertSame("string|null", $type->getPhpTypeOfInput()->asTypeString());
    }

    public function testArrayOfType()
    {
        $type = new ArrayOfType(Field::element()->int()->build());

        $this->assertSame("array<mixed>|null", $type->getPhpTypeOfInput()->asTypeString());
    }
}