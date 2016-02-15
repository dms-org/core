<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\File\IUploadedImage;
use Dms\Core\Form\Field\Processor\Validator\ImageDimensionsValidator;
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
    public function buildPhpTypeOfInput() : \Dms\Core\Model\Type\IType
    {
        return Type::object(IUploadedImage::class);
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors() : array
    {
        $processors = parent::buildProcessors();

        $processors[] = new UploadedImageValidator($this->inputType);

        if ($this->has(self::ATTR_MIN_WIDTH)
                || $this->has(self::ATTR_MAX_WIDTH)
                || $this->has(self::ATTR_MIN_HEIGHT)
                || $this->has(self::ATTR_MAX_HEIGHT)
        ) {
            $processors[] = new ImageDimensionsValidator(
                    $this->inputType,
                    $this->get(self::ATTR_MIN_WIDTH),
                    $this->get(self::ATTR_MAX_WIDTH),
                    $this->get(self::ATTR_MIN_HEIGHT),
                    $this->get(self::ATTR_MAX_HEIGHT)
            );
        }

        return $processors;
    }
}