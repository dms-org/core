<?php

namespace Dms\Core\Form\Field\Builder;

use Dms\Core\Form\Field\Processor\Validator\ImageDimensionsValidator;
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
    public function minWidth($pixels)
    {
        return $this
                ->attr(ImageType::ATTR_MIN_WIDTH, $pixels)
                ->validate(new ImageDimensionsValidator($this->getCurrentProcessedType(), $pixels));
    }

    /**
     * Validates the image width is below the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function maxWidth($pixels)
    {
        return $this
                ->attr(ImageType::ATTR_MAX_WIDTH, $pixels)
                ->validate(new ImageDimensionsValidator($this->getCurrentProcessedType(), null, $pixels));
    }

    /**
     * Validates the image height is above the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function minHeight($pixels)
    {
        return $this
                ->attr(ImageType::ATTR_MIN_HEIGHT, $pixels)
                ->validate(new ImageDimensionsValidator($this->getCurrentProcessedType(), null, null, $pixels));
    }

    /**
     * Validates the image height is below the supplied value.
     *
     * @param int $pixels
     *
     * @return static
     */
    public function maxHeight($pixels)
    {
        return $this
                ->attr(ImageType::ATTR_MAX_HEIGHT, $pixels)
                ->validate(new ImageDimensionsValidator($this->getCurrentProcessedType(), null, null, null, $pixels));
    }

    protected function isImage()
    {
        return true;
    }
}