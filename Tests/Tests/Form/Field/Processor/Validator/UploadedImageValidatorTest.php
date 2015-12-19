<?php

namespace Iddigital\Cms\Core\Tests\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\ImageDimensionsValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\UploadedImageValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class UploadedImageValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new UploadedImageValidator(Type::object(IUploadedImage::class)->nullable());
    }

    /**
     * @inheritDoc
     */
    protected function processedType()
    {
        return Type::object(IUploadedImage::class)->nullable();
    }

    /**
     * @return array[]
     */
    public function successTests()
    {
        return [
                [null],
                [$this->mockUploadedImage(true)],
        ];
    }

    protected function mockUploadedImage($isValid)
    {
        $mock = $this->getMockForAbstractClass(IUploadedImage::class);

        $mock->expects($this->any())
                ->method('isValidImage')
                ->willReturn($isValid);

        return $mock;
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
                [
                        $this->mockUploadedImage(false),
                        new Message(UploadedImageValidator::MESSAGE)
                ],
        ];
    }
}