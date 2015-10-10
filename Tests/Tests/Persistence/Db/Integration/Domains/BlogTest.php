<?php

namespace Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains;

use Iddigital\Cms\Core\Persistence\Db\Mapping\IOrm;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKey;
use Iddigital\Cms\Core\Persistence\Db\Schema\ForeignKeyMode;
use Iddigital\Cms\Core\Persistence\DbRepository;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\HashedPassword;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper\BlogOrm;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper\PostMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper\UserMapper;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Post;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\User;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\UserGender;
use Iddigital\Cms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockDatabase;
use Iddigital\Cms\Core\Tests\Persistence\Db\Mock\MockTable;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BlogTest extends DbIntegrationTest
{
    /**
     * @var UserMapper
     */
    protected $userMapper;

    /**
     * @var DbRepository
     */
    protected $userRepo;

    /**
     * @var PostMapper
     */
    protected $postMapper;

    /**
     * @var DbRepository
     */
    protected $postRepo;

    /**
     * @var MockTable
     */
    protected $userTable;

    /**
     * @var MockTable
     */
    protected $aliasTable;

    /**
     * @var MockTable
     */
    protected $postTable;

    /**
     * @var MockTable
     */
    protected $commentTable;

    /**
     * @var MockTable
     */
    protected $userFriendsJoinTable;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return new BlogOrm();
    }

    /**
     * @inheritDoc
     */
    protected function mapperAndRepoType()
    {
        return User::class;
    }

    public function setUp()
    {
        parent::setUp();
        $this->userMapper = $this->mapper;
        $this->userRepo   = $this->repo;

        $this->postMapper = $this->orm->getEntityMapper(Post::class);
        $this->postRepo   = new DbRepository($this->connection, $this->postMapper);
    }

    /**
     * @inheritDoc
     */
    protected function buildDatabase(MockDatabase $db, IOrm $orm)
    {
        parent::buildDatabase($db, $orm);

        $this->userMapper           = $db->getTable('users');
        $this->aliasTable           = $db->getTable('aliases');
        $this->postTable            = $db->getTable('posts');
        $this->commentTable         = $db->getTable('comments');
        $this->userFriendsJoinTable = $db->getTable('user_friends');
    }

    public function testCreatesCorrectForeignKeys()
    {
        $this->assertEquals([
                new ForeignKey(
                        'fk_aliases_user_id_users',
                        ['user_id'],
                        'users',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->aliasTable->getStructure()->getForeignKeys()));

        $this->assertEquals([
                new ForeignKey(
                        'fk_posts_author_id_users',
                        ['author_id'],
                        'users',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->postTable->getStructure()->getForeignKeys()));

        $this->assertEquals([
                new ForeignKey(
                        'fk_comments_post_id_posts',
                        ['post_id'],
                        'posts',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                ),
                new ForeignKey(
                        'fk_comments_author_id_users',
                        ['author_id'],
                        'users',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::SET_NULL
                )
        ], array_values($this->commentTable->getStructure()->getForeignKeys()));

        $this->assertEquals([
                new ForeignKey(
                        'fk_user_friends_user_id_users',
                        ['user_id'],
                        'users',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                ),
                new ForeignKey(
                        'fk_user_friends_friend_id_users',
                        ['friend_id'],
                        'users',
                        ['id'],
                        ForeignKeyMode::CASCADE,
                        ForeignKeyMode::CASCADE
                )
        ], array_values($this->userFriendsJoinTable->getStructure()->getForeignKeys()));
    }

    /**
     * @return User
     */
    protected function dummyUser()
    {
        return User::register(
                'johnsmith@gmail.com',
                'John',
                'Smith',
                new \DateTimeImmutable('1975-04-23'),
                UserGender::male(),
                new HashedPassword('some--hash', 'bcrypt')
        );
    }

    protected function dummyUserWithId($id)
    {
        $user = $this->dummyUser();
        $user->setId($id);

        return $user;
    }

    /**
     * @return array
     */
    protected function dummyUserDbData($id)
    {
        return [
                'id'                 => $id,
                'first_name'         => 'John',
                'last_name'          => 'Smith',
                'email'              => 'johnsmith@gmail.com',
                'dob'                => '1975-04-23',
                'gender'             => 'male',
                'status'             => 'active',
                'password_hash'      => 'some--hash',
                'password_algorithm' => 'bcrypt'
        ];
    }

    /**
     * @return User
     */
    protected function setUpDummyUserInDb()
    {
        $this->db->setData([
                'users' => [$this->dummyUserDbData(1)]
        ]);

        $user = $this->dummyUserWithId(1);

        return $user;
    }

    ////////////////
    // Persisting //
    ////////////////


    public function testPersistUser()
    {
        $user = $this->dummyUser();

        $this->userRepo->save($user);

        $this->assertSame(1, $user->getId());

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);
    }

    public function testPersistUserWithAlias()
    {
        $user = $this->dummyUser();
        $user->aliasAs('Curry', 'Weaver');

        $this->userRepo->save($user);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [
                        ['id' => 1, 'user_id' => 1, 'first_name' => 'Curry', 'last_name' => 'Weaver'],
                ],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                ]
        ]);
    }

    public function testPersistUserWithFriends()
    {
        $user1 = $this->dummyUser();
        $user2 = $this->dummyUser();

        $this->userRepo->saveAll([$user1, $user2]);

        $user1->makeFriends($user2);

        $this->userRepo->saveAll([$user1, $user2]);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [
                        ['user_id' => 1, 'friend_id' => 2],
                        ['user_id' => 2, 'friend_id' => 1],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    public function testPersistPost()
    {
        $user = $this->setUpDummyUserInDb();

        $post = $user->createPost('Hello World!');

        $this->postRepo->save($post);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);
    }

    public function testPersistPostAndComment()
    {
        $user = $this->setUpDummyUserInDb();
        $post = $user->createPost('Hello World!');
        $user->commentOn($post, 'Hello John!');

        $this->postRepo->save($post);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!'],
                ],
                'comments'     => [
                        ['id' => 1, 'post_id' => 1, 'author_id' => 1, 'content' => 'Hello John!'],
                ],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);
    }

    /////////////
    // Loading //
    /////////////

    public function testLoadUser()
    {
        $this->db->setData([
                'users' => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $user = $this->dummyUserWithId(1);

        $this->assertEquals($user, $this->userRepo->get(1));
    }

    public function testLoadUserWithAlias()
    {
        $this->db->setData([
                'aliases' => [
                        ['id' => 1, 'user_id' => 1, 'first_name' => 'Curry', 'last_name' => 'Weaver'],
                ],
                'users'   => [
                        $this->dummyUserDbData(1),
                ]
        ]);

        $user = $this->dummyUserWithId(1);
        $user->aliasAs('Curry', 'Weaver');
        $user->alias->setId(1);

        $this->assertEquals($user, $this->userRepo->get(1));
    }

    public function testLoadUserWithFriends()
    {
        $this->db->setData([
                'user_friends' => [
                        ['user_id' => 1, 'friend_id' => 2],
                        ['user_id' => 2, 'friend_id' => 1],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        $user1 = $this->dummyUserWithId(1);
        $user2 = $this->dummyUserWithId(2);
        $user1->makeFriends($user2);

        $this->assertEquals([$user1, $user2], $this->userRepo->getAll());
    }

    public function testLoadPost()
    {
        $this->db->setData([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 2, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $user = $this->dummyUserWithId(1);
        $post = $user->createPost('Hello World!');
        $post->setId(2);

        $this->assertEquals($post, $this->postRepo->get(2));
    }

    public function testLoadPostAndComment()
    {
        $this->db->setData([
                'posts'    => [
                        ['id' => 3, 'author_id' => 1, 'content' => 'Hello World!'],
                ],
                'comments' => [
                        ['id' => 5, 'post_id' => 3, 'author_id' => 1, 'content' => 'Hello John!'],
                ],
                'users'    => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $user = $this->dummyUserWithId(1);
        $post = $user->createPost('Hello World!');
        $post->setId(3);
        $comment = $user->commentOn($post, 'Hello John!');
        $comment->setId(5);

        $this->assertEquals($post, $this->postRepo->get(3));
    }



    //////////////////////
    // Persist Existing //
    //////////////////////


    public function testPersistExistingUser()
    {
        $this->db->setData([
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        /** @var User $user */
        $user = $this->repo->get(1);
        $user->firstName = 'Garry';

        $this->userRepo->save($user);

        $dummyUserDbData = $this->dummyUserDbData(1);
        $dummyUserDbData['first_name'] = 'Garry';
        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $dummyUserDbData
                ]
        ]);
    }

    public function testPersistExistingUserWithAlias()
    {
        $this->db->setData([
                'aliases'      => [
                        ['id' => 1, 'user_id' => 1, 'first_name' => 'Curry', 'last_name' => 'Weaver'],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                ]
        ]);

        /** @var User $user */
        $user = $this->repo->get(1);
        $user->aliasAs('Salad', 'Dresser');

        $this->userRepo->save($user);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [
                        ['id' => 2, 'user_id' => 1, 'first_name' => 'Salad', 'last_name' => 'Dresser'],
                ],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                ]
        ]);
    }

    public function testPersistExistingUserWithFriends()
    {
        $this->db->setData([
                'user_friends' => [
                        ['user_id' => 1, 'friend_id' => 2],
                        ['user_id' => 2, 'friend_id' => 1],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        /** @var User $user1 */
        $user1 = $this->repo->get(1);
        /** @var User $user2 */
        $user2 = $this->repo->get(2);
        $user1->unfriend($user2);

        $this->userRepo->saveAll([$user1, $user2]);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    public function testPersistExistingPost()
    {
        $this->db->setData([
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        /** @var Post $post */
        $post = $this->postRepo->get(1);
        $post->content = 'ABC';

        $this->postRepo->save($post);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'ABC']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);
    }

    public function testUpdatePostsAuthorThroughUsers()
    {
        $this->db->setData([
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        /** @var Post $post */
        $post = $this->postRepo->get(1);
        /** @var User $author */
        $author = $this->userRepo->get(1);
        /** @var User $otherUser */
        $otherUser = $this->userRepo->get(2);

        $author->transferPostTo($post, $otherUser);

        $this->userRepo->saveAll([$author, $otherUser]);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 2, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    public function testUpdatePostAuthorThroughAuthorId()
    {
        $this->db->setData([
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        /** @var Post $post */
        $post = $this->postRepo->get(1);
        $post->authorId = 2;

        $this->postRepo->save($post);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 2, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    public function testPersistExistingPostAndComment()
    {
        $this->db->setData([
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!'],
                ],
                'comments'     => [
                        ['id' => 1, 'post_id' => 1, 'author_id' => 1, 'content' => 'Hello John!'],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        /** @var User $user */
        $user = $this->userRepo->get(2);
        /** @var Post $post */
        $post = $this->postRepo->get(1);
        $user->commentOn($post, 'Goodbye!');

        $this->postRepo->save($post);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 1, 'author_id' => 1, 'content' => 'Hello World!'],
                ],
                'comments'     => [
                        ['id' => 1, 'post_id' => 1, 'author_id' => 1, 'content' => 'Hello John!'],
                        ['id' => 2, 'post_id' => 1, 'author_id' => 2, 'content' => 'Goodbye!'],
                ],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    //////////////
    // Removing //
    //////////////

    public function testRemoveUser()
    {
        $this->db->setData([
                'users' => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $this->userRepo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [],
        ]);
    }

    public function testRemoveUserWithAlias()
    {
        $this->db->setData([
                'aliases' => [
                        ['id' => 1, 'user_id' => 1, 'first_name' => 'Curry', 'last_name' => 'Weaver'],
                ],
                'users'   => [
                        $this->dummyUserDbData(1),
                ]
        ]);

        $this->userRepo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [],
        ]);
    }

    public function testRemoveUserWithFriends()
    {
        $this->db->setData([
                'user_friends' => [
                        ['user_id' => 1, 'friend_id' => 2],
                        ['user_id' => 2, 'friend_id' => 1],
                ],
                'users'        => [
                        $this->dummyUserDbData(1),
                        $this->dummyUserDbData(2),
                ]
        ]);

        $this->userRepo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(2),
                ]
        ]);
    }

    public function testRemovePost()
    {
        $this->db->setData([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 2, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $this->postRepo->removeById(2);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1),
                ]
        ]);
    }

    public function testRemovePostsAuthor()
    {
        $this->db->setData([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 2, 'author_id' => 1, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $this->userRepo->removeById(1);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [
                        ['id' => 2, 'author_id' => null, 'content' => 'Hello World!']
                ],
                'comments'     => [],
                'user_friends' => [],
                'users'        => []
        ]);
    }

    public function testRemovePostAndComment()
    {
        $this->db->setData([
                'posts'    => [
                        ['id' => 3, 'author_id' => 1, 'content' => 'Hello World!'],
                ],
                'comments' => [
                        ['id' => 5, 'post_id' => 3, 'author_id' => 1, 'content' => 'Hello John!'],
                ],
                'users'    => [
                        $this->dummyUserDbData(1)
                ]
        ]);

        $this->postRepo->removeById(3);

        $this->assertDatabaseDataSameAs([
                'aliases'      => [],
                'posts'        => [],
                'comments'     => [],
                'user_friends' => [],
                'users'        => [
                        $this->dummyUserDbData(1)
                ]
        ]);
    }
}