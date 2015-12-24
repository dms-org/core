<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageEntity extends Entity
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $subTitle;

    /**
     * @var string
     */
    public $content;

    /**
     * PageEntity constructor.
     *
     * @param string      $title
     * @param null|string $subTitle
     * @param string      $content
     */
    public function __construct($title, $subTitle, $content)
    {
        parent::__construct();
        $this->title    = $title;
        $this->subTitle = $subTitle;
        $this->content  = $content;
    }

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->title)->asString();
        $class->property($this->subTitle)->asString();
        $class->property($this->content)->asString();
    }


}