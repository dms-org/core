<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\File\IFile;
use Dms\Core\File\IImage;
use Dms\Core\File\IUploadedFile;
use Dms\Core\Model\Type\Builder\Type;

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
    public function __construct(bool $isImage, string $path, callable $fileNameCallback)
    {
        parent::__construct(Type::object($isImage ? IImage::class : IFile::class));

        $this->path             = $path;
        $this->fileNameCallback = $fileNameCallback;
    }

    public static function withClientFileName($isImage, $path)
    {
        return new self($isImage, $path, function (IUploadedFile $file) {
            return $file->getClientFileName() ?: $file->getName();
        });
    }

    public static function withRandomFileName($isImage, $path, $fileNameLength = 16)
    {
        return new self($isImage, $path, function (IUploadedFile $file) use ($fileNameLength) {
            $alphaNumeric = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

            return substr(str_shuffle($alphaNumeric), 0, $fileNameLength) . '.' . $file->getExtension();
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