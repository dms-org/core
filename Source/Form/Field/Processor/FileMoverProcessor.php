<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\File\IFile;
use Iddigital\Cms\Core\File\IImage;
use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

/**
 * The file mover processor processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileMoverProcessor extends FieldProcessor
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var callable
     */
    private $fileNameCallback;

    /**
     * @param bool     $isImage
     * @param string   $path
     * @param callable $fileNameCallback
     */
    public function __construct($isImage, $path, callable $fileNameCallback)
    {
        parent::__construct(Type::object($isImage ? IImage::class : IFile::class));

        $this->path             = $path;
        $this->fileNameCallback = $fileNameCallback;
    }

    public static function getClientFileName(IUploadedFile $file)
    {
        return $file->getClientFileName();
    }

    public static function withClientFileName($isImage, $path)
    {
        return new self($isImage, $path, function (IUploadedFile $file) {
            return $file->getClientFileName();
        });
    }

    public static function withRandomFileName($isImage, $path, $fileNameLength = 16)
    {
        return new self($isImage, $path, function (IUploadedFile $file) use ($fileNameLength) {
            return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,
                    $fileNameLength) . '.' . $file->getExtension();
        });
    }

    protected function doProcess($input, array &$messages)
    {
        /** @var IUploadedFile $input */
        return $input->moveTo($this->path . DIRECTORY_SEPARATOR . call_user_func($this->fileNameCallback, $input));
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}