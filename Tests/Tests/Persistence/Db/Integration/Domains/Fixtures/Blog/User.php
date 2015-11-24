<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Iddigital\Cms\Core\Model\EntityIdCollection;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\Entity;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class User extends Entity
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $firstName;

    /**
     * @var string
     */
    public $lastName;

    /**
     * @var HashedPassword
     */
    public $password;

    /**
     * @var \DateTimeImmutable
     */
    public $dateOfBirth;

    /**
     * @var UserGender
     */
    public $gender;

    /**
     * @var UserStatus
     */
    public $status;

    /**
     * @var EntityIdCollection|int[]
     */
    public $postIds;

    /**
     * @var EntityIdCollection|int[]
     */
    public $friendIds;

    /**
     * @var EntityIdCollection|int[]
     */
    public $commentIds;

    /**
     * @var Alias|null
     */
    public $alias;

    /**
     * Defines the structure of this entity.
     *
     * @param ClassDefinition $class
     */
    protected function defineEntity(ClassDefinition $class)
    {
        $class->property($this->email)->asString();
        $class->property($this->firstName)->asString();
        $class->property($this->lastName)->asString();
        $class->property($this->password)->asObject(HashedPassword::class);
        $class->property($this->dateOfBirth)->asObject(\DateTimeImmutable::class);
        $class->property($this->gender)->asObject(UserGender::class);
        $class->property($this->status)->asObject(UserStatus::class);
        $class->property($this->postIds)->asCollectionOf(Type::int());
        $class->property($this->friendIds)->asCollectionOf(Type::int());
        $class->property($this->commentIds)->asCollectionOf(Type::int());
        $class->property($this->alias)->asObject(Alias::class);
    }

    /**
     * @inheritDoc
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->postIds   = new EntityIdCollection();
        $this->friendIds = new EntityIdCollection();
        $this->commentIds = new EntityIdCollection();
    }


    public static function register($email, $firstName, $lastName, \DateTimeImmutable $dateOfBirth, UserGender $gender, HashedPassword $password)
    {
        $user = new self();

        $user->email       = $email;
        $user->firstName   = $firstName;
        $user->lastName    = $lastName;
        $user->dateOfBirth = $dateOfBirth;
        $user->gender      = $gender;
        $user->status      = UserStatus::active();
        $user->password    = $password;

        return $user;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     *
     * @return void
     */
    public function aliasAs($firstName, $lastName)
    {
        $this->alias = Alias::create($firstName, $lastName);
    }

    /**
     * @param string $content
     *
     * @return Post
     */
    public function createPost($content)
    {
        return Post::create($this, $content);
    }

    /**
     * @param Post   $post
     * @param string $content
     *
     * @return Comment
     */
    public function commentOn(Post $post, $content)
    {
        $post->comments[] = $comment = Comment::create($this, $content);

        return $comment;
    }

    /**
     * @param User $otherUser
     *
     * @return void
     */
    public function makeFriends(User $otherUser)
    {
        $this->friendIds[]      = $otherUser->getId();
        $otherUser->friendIds[] = $this->getId();
    }

    /**
     * @param User $otherUser
     *
     * @return void
     */
    public function unfriend(User $otherUser)
    {
        $this->friendIds->remove($otherUser->getId());
        $otherUser->friendIds->remove($this->getId());
    }

    /**
     * @param Post $post
     * @param User $otherUser
     *
     * @return void
     */
    public function transferPostTo(Post $post, User $otherUser)
    {
        $post->authorId = $otherUser->getId();
        $this->postIds->remove($post->getId());
        $otherUser->postIds[] = $post->getId();
    }
}