<?php

namespace Iddigital\Cms\Core\Tests\Integration\Fixtures\Page\Domain;

use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Util\IClock;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Page extends Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var \DateTime
     * @immutable
     */
    public $createdAt;

    /**
     * @var \DateTime
     */
    public $updatedAt;

    /**
     * @var Seo
     */
    public $seo;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->title)->asString();

        $class->property($this->content)->asString();

        $class->property($this->createdAt)->immutable()->asObject(\DateTime::class);

        $class->property($this->updatedAt)->asObject(\DateTime::class);

        $class->property($this->seo)->asObject(Seo::class);
    }

    public static function createNew($title, $content, Seo $seo, IClock $clock)
    {
        $page = self::construct();

        $page->title     = $title;
        $page->content   = $content;
        $page->seo       = $seo;
        $page->createdAt = $clock->now();
        $page->updatedAt = $clock->now();

        return $page;
    }
}