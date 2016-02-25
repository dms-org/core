<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\File\IFile;
use Dms\Core\File\IImage;
use Dms\Core\File\IUploadedFile;
use Dms\Core\File\UploadedFileProxy;
use Dms\Core\File\UploadedImageProxy;
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
     * @param string   $processedFileClass
     * @param string   $path
     * @param callable $fileNameCallback
     */
    public function __construct(string $processedFileClass, string $path, callable $fileNameCallback)
    {
        parent::__construct(Type::object($processedFileClass));

        $this->path             = $path;
        $this->fileNameCallback = $fileNameCallback;
    }

    public static function withClientFileName(string $processedFileClass, string $path)
    {
        return new self($processedFileClass, $path, function (IUploadedFile $file) {
            return $file->getClientFileName() ?: $file->getName();
        });
    }

    public static function withRandomFileName(string $processedFileClass, string $path, int $fileNameLength = 16)
    {
        return new self($processedFileClass, $path, function (IUploadedFile $file) use ($fileNameLength) {
            $randomName = substr(str_replace(['+', '=', '/'], '', base64_encode(openssl_random_pseudo_bytes($fileNameLength * 2))), 0, $fileNameLength);

            return $randomName . '.' . $file->getExtension();
        });
    }

    protected function doProcess($input, array &$messages)
    {
        /** @var IUploadedFile $input */
        return $input->moveTo($this->path . DIRECTORY_SEPARATOR . call_user_func($this->fileNameCallback, $input));
    }

    protected function doUnprocess($input)
    {
        if ($input instanceof IImage) {
            return new UploadedImageProxy($input);
        } elseif ($input instanceof IFile) {
            return new UploadedFileProxy($input);
        }

        throw InvalidArgumentException::format('Unknown file class: %s', get_class($input));
    }
}