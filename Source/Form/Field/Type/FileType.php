<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\File\IUploadedFile;
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
    public function buildPhpTypeOfInput()
    {
        return Type::object(IUploadedFile::class);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
            new UploadedFileValidator($this->inputType)
        ];
    }
}