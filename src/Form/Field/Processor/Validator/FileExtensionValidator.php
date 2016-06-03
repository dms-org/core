<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\File\IUploadedFile;
use Dms\Core\File\UploadedFileProxy;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\IType;

/**
 * The file extensions validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileExtensionValidator extends FieldValidator
{
    const MESSAGE = 'validation.file-extensions';

    /**
     * @var string[]|null;
     */
    private $extensions;

    public function __construct(IType $inputType, array $extensions)
    {
        parent::__construct($inputType);
        $this->extensions = $extensions;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        /** @var IUploadedFile $input */

        if ($input instanceof UploadedFileProxy) {
            return;
        }

        $fileName = $input->getClientFileNameWithFallback();

        $extension = strpos($fileName, '.') === false
            ? false
            : substr($fileName, strrpos($fileName, '.') + 1);

        if ($extension === false || !in_array(strtoupper($extension), array_map('strtoupper', $this->extensions), true)) {
            $messages[] = new Message(self::MESSAGE, ['extensions' => implode(', ', $this->extensions)]);
        }
    }
}