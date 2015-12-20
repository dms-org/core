<?php

namespace Dms\Core\Form;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The form section class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormSection implements IFormSection
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var IField[]
     */
    private $fields;

    public function __construct($title, array $fields)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fields', $fields, IField::class);

        $this->title  = $title;
        $this->fields = $fields;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields()
    {
        return $this->fields;
    }
}