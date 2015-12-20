<?php

namespace Dms\Core\Tests\Form\Field\Processor\Validator;

use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Form\Field\Processor\Validator\ImageDimensionsValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImageDimensionsValidatorTest extends FieldValidatorTest
{
    /**
     * @return FieldValidator
     */
    protected function validator()
    {
        return new ImageDimensionsValidator(
            Type::object(IUploadedImage::class)->nullable(),
            200, // min-width
            300, // max-width
            500, // min-height
            750  // max-height
        );
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
            [$this->mockUploadedImage(200, 500)],
            [$this->mockUploadedImage(300, 750)],
            [$this->mockUploadedImage(250, 600)],
            [$this->mockUploadedImage(200, 749)],
            [$this->mockUploadedImage(201, 500)],
            [$this->mockUploadedImage(232, 644)],
        ];
    }

    protected function mockUploadedImage($width, $height)
    {
        $mock = $this->getMockForAbstractClass(IUploadedImage::class);

        $mock->expects($this->any())
            ->method('getWidth')
            ->willReturn($width);

        $mock->expects($this->any())
            ->method('getHeight')
            ->willReturn($height);

        return $mock;
    }

    /**
     * @return array[]
     */
    public function failTests()
    {
        return [
            [
                $this->mockUploadedImage(199, 600),
                new Message(ImageDimensionsValidator::MESSAGE_MIN_WIDTH, ['min_width' => 200])
            ],
            [
                $this->mockUploadedImage(301, 600),
                new Message(ImageDimensionsValidator::MESSAGE_MAX_WIDTH, ['max_width' => 300])
            ],
            [
                $this->mockUploadedImage(250, 499),
                new Message(ImageDimensionsValidator::MESSAGE_MIN_HEIGHT, ['min_height' => 500])
            ],
            [
                $this->mockUploadedImage(250, 751),
                new Message(ImageDimensionsValidator::MESSAGE_MAX_HEIGHT, ['max_height' => 750])
            ],
            [
                $this->mockUploadedImage(1, 1),
                [
                    new Message(ImageDimensionsValidator::MESSAGE_MIN_WIDTH, ['min_width' => 200]),
                    new Message(ImageDimensionsValidator::MESSAGE_MIN_HEIGHT,
                        ['min_height' => 500]),
                ]
            ],
            [
                $this->mockUploadedImage(1000, 1000),
                [
                    new Message(ImageDimensionsValidator::MESSAGE_MAX_WIDTH, ['max_width' => 300]),
                    new Message(ImageDimensionsValidator::MESSAGE_MAX_HEIGHT,
                        ['max_height' => 750]),
                ]
            ],
        ];
    }
}