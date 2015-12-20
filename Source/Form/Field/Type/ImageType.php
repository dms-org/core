<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\Validator\UploadedFileValidator;
use Dms\Core\Form\Field\Processor\Validator\UploadedImageValidator;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The image file type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImageType extends FileType
{
    const ATTR_MIN_WIDTH = 'min-width';
    const ATTR_MAX_WIDTH = 'max-width';

    const ATTR_MIN_HEIGHT = 'min-height';
    const ATTR_MAX_HEIGHT = 'max-height';

    /**
     * {@inheritdoc}
     */
    public function buildPhpTypeOfInput()
    {
        return Type::object(IUploadedImage::class);
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors()
    {
        return [
                new UploadedFileValidator($this->inputType),
                new UploadedImageValidator($this->inputType),
        ];
    }


}