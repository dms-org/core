<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IEntityCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestPost extends Entity
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
     * @var IEntityCollection|TestComment[]
     */
    public $comments;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->authorId)->asInt();
        $class->property($this->comments)->asCollectionOf(Type::object(TestComment::class));
        $class->property($this->content)->asString();
    }

    /**
     * Post constructor.
     *
     * @param int|null $id
     * @param TestUser $author
     * @param string   $content
     */
    public function __construct($id, TestUser $author, $content)
    {
        parent::__construct($id);

        $this->authorId = $author->getId();
        $this->comments = new EntityCollection(TestComment::class);
        $this->content  = $content;
    }

    /**
     * @param TestUser $author
     * @param string   $content
     *
     * @return TestPost
     */
    public static function create(TestUser $author, $content)
    {
        return new self(null, $author, $content);
    }
}