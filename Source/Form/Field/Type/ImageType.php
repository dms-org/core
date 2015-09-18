<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\File\IUploadedImage;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\ObjectType;

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
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [];
    }
}