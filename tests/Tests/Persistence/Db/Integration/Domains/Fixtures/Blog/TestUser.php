<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog;

use Dms\Core\Model\EntityIdCollection;
use Dms\Core\Model\Object\ClassDefinition;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Model\Type\Builder\Type;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TestUser extends Entity
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
     * @var TestHashedPassword
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
     * @var EntityIdCollection
     */
    public $postIds;

    /**
     * @var EntityIdCollection
     */
    public $friendIds;

    /**
     * @var EntityIdCollection
     */
    public $commentIds;

    /**
     * @var TestAlias|null
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
        $class->property($this->password)->asObject(TestHashedPassword::class);
        $class->property($this->dateOfBirth)->asObject(\DateTimeImmutable::class);
        $class->property($this->gender)->asObject(UserGender::class);
        $class->property($this->status)->asObject(UserStatus::class);
        $class->property($this->postIds)->asType(EntityIdCollection::type());
        $class->property($this->friendIds)->asType(EntityIdCollection::type());
        $class->property($this->commentIds)->asType(EntityIdCollection::type());
        $class->property($this->alias)->asObject(TestAlias::class);
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


    public static function register($email, $firstName, $lastName, \DateTimeImmutable $dateOfBirth, UserGender $gender, TestHashedPassword $password)
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
        $this->alias = TestAlias::create($firstName, $lastName);
    }

    /**
     * @param string $content
     *
     * @return TestPost
     */
    public function createPost($content)
    {
        return TestPost::create($this, $content);
    }

    /**
     * @param TestPost $post
     * @param string   $content
     *
     * @return TestComment
     */
    public function commentOn(TestPost $post, $content)
    {
        $post->comments[] = $comment = TestComment::create($this, $content);

        return $comment;
    }

    /**
     * @param TestUser $otherUser
     *
     * @return void
     */
    public function makeFriends(TestUser $otherUser)
    {
        $this->friendIds->addRange([$otherUser->getId()]);
        $otherUser->friendIds->addRange([$this->getId()]);
    }

    /**
     * @param TestUser $otherUser
     *
     * @return void
     */
    public function unfriend(TestUser $otherUser)
    {
        $this->friendIds->remove($otherUser->getId());
        $otherUser->friendIds->remove($this->getId());
    }

    /**
     * @param TestPost $post
     * @param TestUser $otherUser
     *
     * @return void
     */
    public function transferPostTo(TestPost $post, TestUser $otherUser)
    {
        $post->authorId = $otherUser->getId();
        $this->postIds->remove($post->getId());
        $otherUser->postIds->addRange([$post->getId()]);
    }
}
