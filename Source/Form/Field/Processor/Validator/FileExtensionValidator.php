<?php

namespace Iddigital\Cms\Core\Form\Field\Processor\Validator;

use Iddigital\Cms\Core\File\IUploadedFile;
use Iddigital\Cms\Core\Form\Field\Processor\FieldValidator;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType;

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
        if (!in_array(strtoupper($input->getExtension()), array_map('strtoupper', $this->extensions), true)) {
            $messages[] = new Message(self::MESSAGE, ['extensions' => implode(', ', $this->extensions)]);
        }
    }
}