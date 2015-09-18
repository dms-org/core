<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\ValueObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Seo extends ValueObject
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
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    protected function define(ClassDefinition $class)
    {
        $class->property($this->title)->asString();

        $class->property($this->description)->asString();

        $class->property($this->keywords)->asArrayOf(Type::string());
    }

    public static function build($title, $description, array $keywords)
    {
        $self = self::construct();

        $self->title       = $title;
        $self->description = $description;
        $self->keywords    = $keywords;

        return $self;
    }
}