<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Model\Type\ScalarType;

/**
 * The field type processor.
 * 
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeProcessor extends FieldProcessor
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        parent::__construct(new ScalarType(strtolower($type)));

        $this->type = $type;
    }

    protected function doProcess($input, array &$messages)
    {
        settype($input, $this->type);

        return $input;
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}