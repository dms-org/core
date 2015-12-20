<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Comment extends Entity
{
    /**
     * @var int
     */
    public $authorId;

    /**
     * @var string
     */
    public $content;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->authorId)->asInt();
        $class->property($this->content)->asString();
    }

    /**
     * Post constructor.
     *
     * @param int|null $id
     * @param User     $author
     * @param string   $content
     */
    public function __construct($id, User $author, $content)
    {
        parent::__construct($id);
        $this->authorId = $author->getId();
        $this->content  = $content;
    }

    /**
     * @param User   $author
     * @param string $content
     *
     * @return Comment
     */
    public static function create(User $author, $content)
    {
        return new self(null, $author, $content);
    }
}