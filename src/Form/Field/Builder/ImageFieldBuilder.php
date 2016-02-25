<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\File\IImage;
use Dms\Core\Form\Field\Type\ImageType;

/**
 * The image field builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ImageFieldBuilder extends FileFieldBuilder
{
    /**
     * Validates the image width is above the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function minWidth(int $pixels)
    {
        return $this->attr(ImageType::ATTR_MIN_WIDTH, $pixels);
    }

    /**
     * Validates the image width is below the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function maxWidth(int $pixels)
    {
        return $this->attr(ImageType::ATTR_MAX_WIDTH, $pixels);
    }

    /**
     * Validates the image height is above the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function minHeight(int $pixels)
    {
        return $this->attr(ImageType::ATTR_MIN_HEIGHT, $pixels);
    }

    /**
     * Validates the image height is below the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function maxHeight(int $pixels)
    {
        return $this->attr(ImageType::ATTR_MAX_HEIGHT, $pixels);
    }

    protected function movedClassName() : string
    {
        return IImage::class;
    }
}