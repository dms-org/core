<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
        return [];
    }
}