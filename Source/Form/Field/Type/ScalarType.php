<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The scalar type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ScalarType extends FieldType
{
    const STRING = 'string';
    const INT = 'int';
    const FLOAT = 'float';
    const BOOL = 'bool';

    /**
     * @var string
     */
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    protected function buildPhpTypeOfInput()
    {
        // Scalar inputs can be of mixed type
        // as they will be validated and coerced
        // into their expected php types by the
        // field processors
        return Type::mixed();
    }
}