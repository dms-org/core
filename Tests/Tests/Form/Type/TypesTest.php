<?php

namespace Dms\Core\Tests\Form\Type;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\File\IUploadedFile;
use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Form\Field\Type\ArrayOfType;
use Dms\Core\Form\Field\Type\BoolType;
use Dms\Core\Form\Field\Type\DateTimeType;
use Dms\Core\Form\Field\Type\DateType;
use Dms\Core\Form\Field\Type\FileType;
use Dms\Core\Form\Field\Type\FloatType;
use Dms\Core\Form\Field\Type\ImageType;
use Dms\Core\Form\Field\Type\IntType;
use Dms\Core\Form\Field\Type\StringType;
use Dms\Core\Form\Field\Type\TimeOfDayType;

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
        $type = new TimeOfDayType('H:i');

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