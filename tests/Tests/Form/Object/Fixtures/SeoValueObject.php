<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class SeoValueObject extends ValueObject
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string[]
     */
    public $keywords;

    /**
     * SeoValueObject constructor.
     *
     * @param string    $title
     * @param string    $description
     * @param \string[] $keywords
     */
    public function __construct($title, $description, array $keywords)
    {
        parent::__construct();
        $this->title       = $title;
        $this->description = $description;
        $this->keywords    = $keywords;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->title)->asString();
        $class->property($this->description)->asString();
        $class->property($this->keywords)->asArrayOf(Type::string());
    }
}