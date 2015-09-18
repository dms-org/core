<?php

namespace Iddigital\Cms\Core\Tests\Form\Object\Fixtures;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Object\ValueObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

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