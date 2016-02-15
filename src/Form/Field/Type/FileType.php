<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\File\IUploadedFile;
use Dms\Core\Form\Field\Processor\Validator\FileExtensionValidator;
use Dms\Core\Form\Field\Processor\Validator\FileSizeValidator;
use Dms\Core\Form\Field\Processor\Validator\UploadedFileValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The file type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileType extends FieldType
{
    const ATTR_EXTENSIONS = 'extensions';
    const ATTR_MIN_SIZE = 'min-size';
    const ATTR_MAX_SIZE = 'max-size';

    /**
     * {@inheritdoc}
     */
    public function buildPhpTypeOfInput() : \Dms\Core\Model\Type\IType
    {
        return Type::object(IUploadedFile::class);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        $processors = [new UploadedFileValidator($this->inputType)];

        if ($this->has(self::ATTR_EXTENSIONS)) {
            $processors[] = new FileExtensionValidator($this->inputType, $this->get(self::ATTR_EXTENSIONS));
        }

        if ($this->has(self::ATTR_MIN_SIZE) || $this->has(self::ATTR_MAX_SIZE)) {
            $processors[] = new FileSizeValidator(
                    $this->inputType,
                    $this->get(self::ATTR_MIN_SIZE),
                    $this->get(self::ATTR_MAX_SIZE)
            );
        }

        return $processors;
    }
}