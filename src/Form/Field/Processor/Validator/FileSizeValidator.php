<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor\Validator;

use Dms\Core\File\IUploadedFile;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The file size validator.
 * This assumes it has already been validated as a file.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileSizeValidator extends FieldValidator
{
    const MESSAGE_MIN = 'validation.file-size.min';
    const MESSAGE_MAX = 'validation.file-size.max';

    /**
     * @var int|null
     */
    private $minSize = null;

    /**
     * @var int|null
     */
    private $maxSize = null;

    /**
     * @param IType    $inputType
     * @param int|null $minSize
     * @param int|null $maxSize
     */
    public function __construct(IType $inputType, $minSize, $maxSize)
    {
        parent::__construct($inputType);
        $this->minSize = $minSize;
        $this->maxSize = $maxSize;
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($input, array &$messages)
    {
        /** @var IUploadedFile $input */

        $fileSize = $input->getSize();

        if ($this->minSize !== null && $fileSize < $this->minSize) {
            $messages[] = new Message(self::MESSAGE_MIN, ['min_size' => $this->minSize]);
        }

        if ($this->maxSize !== null && $fileSize > $this->maxSize) {
            $messages[] = new Message(self::MESSAGE_MAX, ['max_size' => $this->maxSize]);
        }
    }
}