<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Model\Type\ScalarType;
use Pinq\Analysis\IType;

/**
 * The trim processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TrimProcessor extends FieldProcessor
{
    /**
     * @var string
     */
    private $characters;

    public function __construct($characters = null)
    {
        parent::__construct(new ScalarType(ScalarType::STRING));
        $this->characters = $characters;
    }

    protected function doProcess($input, array &$messages)
    {
        if ($this->characters) {
            return trim($input, $this->characters);
        } else {
            return trim($input);
        }
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}