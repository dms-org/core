<?php

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The image dimensions validator.
 * This assumes it has already been validated as an image.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImageDimensionsValidator extends FieldValidator
{
    const MESSAGE_MAX_WIDTH = 'validation.image-dimensions.width.max';
    const MESSAGE_MIN_WIDTH = 'validation.image-dimensions.width.min';
    const MESSAGE_MAX_HEIGHT = 'validation.image-dimensions.height.max';
    const MESSAGE_MIN_HEIGHT = 'validation.image-dimensions.height.min';

    /**
     * @var int|null
     */
    private $minWidth;

    /**
     * @var int|null
     */
    private $maxWidth;

    /**
     * @var int|null
     */
    private $minHeight;

    /**
     * @var int|null
     */
    private $maxHeight;

    /**
     * @param IType    $inputType
     * @param int|null $minWidth
     * @param int|null $maxWidth
     * @param int|null $minHeight
     * @param int|null $maxHeight
     */
    public function __construct(
            IType $inputType,
            $minWidth = null,
            $maxWidth = null,
            $minHeight = null,
            $maxHeight = null
    ) {
        parent::__construct($inputType);
        $this->minWidth  = $minWidth;
        $this->maxWidth  = $maxWidth;
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
    }

    protected function validateInputType(IType $type)
    {
        if ($type->nonNullable()->asTypeString() !== IUploadedImage::class) {
            throw InvalidArgumentException::format(
                    'Invalid type for image validator: %s, expecting %s',
                    $type->asTypeString(), IUploadedImage::class
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        /** @var IUploadedImage $input */
        $this->validateDimensions($input->getWidth(), $input->getHeight(), $messages);
    }

    /**
     * @param int       $width
     * @param int       $height
     * @param Message[] $messages
     */
    private function validateDimensions($width, $height, array &$messages)
    {
        if ($this->minWidth && $width < $this->minWidth) {
            $messages[] = new Message(self::MESSAGE_MIN_WIDTH, ['min_width' => $this->minWidth]);
        }

        if ($this->maxWidth && $width > $this->maxWidth) {
            $messages[] = new Message(self::MESSAGE_MAX_WIDTH, ['max_width' => $this->maxWidth]);
        }

        if ($this->minHeight && $height < $this->minHeight) {
            $messages[] = new Message(self::MESSAGE_MIN_HEIGHT, ['min_height' => $this->minHeight]);
        }

        if ($this->maxHeight && $height > $this->maxHeight) {
            $messages[] = new Message(self::MESSAGE_MAX_HEIGHT, ['max_height' => $this->maxHeight]);
        }
    }
}