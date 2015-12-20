<?php

namespace Dms\Core\Form\Field\Options;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IFieldOption;
use Dms\Core\Form\IFieldOptions;

/**
 * The field options class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayFieldOptions implements IFieldOptions
{
    /**
     * @var IFieldOption[]
     */
    private $options;

    public function __construct(array $options)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'options', $options, IFieldOption::class);
        $this->options = $options;
    }

    /**
     * Constructs an array field options collections with the option
     * values as the array keys and the labels as the array values.
     *
     * @param array $keyValueOptions
     *
     * @return ArrayFieldOptions
     */
    public static function fromAssocArray(array $keyValueOptions)
    {
        $options = [];

        foreach ($keyValueOptions as $value => $label) {
            $options[] = new FieldOption($value, $label);
        }

        return new self($options);
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->options;
    }
}