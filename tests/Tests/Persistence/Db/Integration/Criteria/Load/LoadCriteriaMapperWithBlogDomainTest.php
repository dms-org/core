<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria\Load;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Criteria\MappedLoadQuery;
use Dms\Core\Persistence\Db\Criteria\MemberExpressionMapper;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToManyRelationMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEmbeddedObjectMapping;
use Dms\Core\Persistence\Db\Criteria\MemberMapping\ToOneEntityRelationMapping;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToManyMemberRelation;
use Dms\Core\Persistence\Db\Mapping\ReadModel\Relation\ToOneMemberRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\Mode\NonIdentifyingRelationMode;
use Dms\Core\Persistence\Db\Mapping\Relation\Reference\ToOneRelationObjectReference;
use Dms\Core\Persistence\Db\Mapping\Relation\ToManyRelation;
use Dms\Core\Persistence\Db\Mapping\Relation\ToOneRelation;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper\BlogOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class LoadCriteriaMapperWithBlogDomainTest extends LoadCriteriaMapperTestBase
{
    protected function buildMapper()
    {
        return new CriteriaMapper(
            (new BlogOrm())->getEntityMapper(TestUser::class),
            $this->getMockForAbstractClass(IConnection::class)
        );
    }

    public function testLoadSimpleProperties()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll(['email', 'firstName', 'lastName']);

        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addRawColumn('email')
                ->addAliasedRawColumn('firstName', 'first_name')
                ->addAliasedRawColumn('lastName', 'last_name'),
            ['email' => 'email', 'firstName' => 'firstName', 'lastName' => 'lastName'], []
        ));
    }

    public function testLoadThisLoadsObjectInstanceViaRelation()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll(['this']);

        $objectMapper = $this->mapper->getMapper();
        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()->addAliasedRawColumn('this_id', 'id'),
            [], [
                'this' => [
                    new ToOneMemberRelation(
                        new ToOneEntityRelationMapping($objectMapper, [], new ToOneRelation(
                            MemberExpressionMapper::SELF_RELATION_ID,
                            new ToOneRelationObjectReference($objectMapper),
                            $objectMapper->getPrimaryTable()->getPrimaryKeyColumnName(),
                            new NonIdentifyingRelationMode()
                        ))
                    ),
                    $this->tables['users'],
                    ['this_id' => 'id'],
                ],
            ]
        ));
    }

    public function testLoadEmbeddedObjects()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll(['password', 'password.algorithm']);

        $objectMapper = $this->mapper->getMapper();
        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addAliasedRawColumn('password.algorithm', 'password_algorithm')
                ->addAliasedRawColumn('password_password_algorithm', 'password_algorithm')
                ->addAliasedRawColumn('password_password_hash', 'password_hash'),
            ['password.algorithm' => 'password.algorithm'],
            [
                'password' => [
                    new ToOneMemberRelation(
                        (new ToOneEmbeddedObjectMapping($objectMapper, [],
                            $objectMapper->getDefinition()->getRelationMappedToProperty('password')))->withoutRelationsToSubSelect(1)
                    ),
                    $this->tables['users'],
                    ['password_password_algorithm' => 'password_algorithm', 'password_password_hash' => 'password_hash'],
                ],
            ]
        ));
    }

    public function testLoadRelatedEntity()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll(['alias']);

        $objectMapper = $this->mapper->getMapper();
        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addAliasedRawColumn('alias_id', 'id')
                ->join(Join::left($this->tables['aliases'], 'aliases', [
                    Expr::equal(
                        $this->tableColumn('users', 'id'),
                        $this->tableColumn('aliases', 'user_id')
                    ),
                ])),
            [],
            [
                'alias' => [
                    new ToOneMemberRelation(
                        (new ToOneEntityRelationMapping($objectMapper, [],
                            $objectMapper->getDefinition()->getRelationMappedToProperty('alias')))->withoutRelationsToSubSelect(1)
                    ),
                    $this->tables['users'],
                    ['alias_id' => 'id'],
                ],
            ]
        ));
    }

    public function testMultipleLoadAllAggregatesInLoad()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'loadAll(postIds).count()'   => 'posts-count',
                'loadAll(friendIds).count()' => 'friends-count',
            ]);

        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addColumn('posts-count', Expr::subSelect(
                    Select::from($this->tables['posts'])
                        ->addColumn('__single_val', Expr::count())
                        ->where(Expr::equal(
                            $this->column('id'),
                            $this->tableColumn('posts', 'author_id')
                        ))
                ))
                ->addColumn('friends-count', Expr::subSelect(
                    Select::from($this->tables['users'])
                        ->setAlias('users1')
                        ->addColumn('__single_val', Expr::count())
                        ->join(Join::inner($this->tables['user_friends'], 'user_friends', [
                            Expr::equal(
                                $this->tableColumn('user_friends', 'user_id'),
                                Expr::column('users1', $this->tables['users']->getColumn('id'))
                            ),
                        ]))
                        ->where(Expr::equal(
                            $this->tableColumn('users', 'id'),
                            $this->tableColumn('user_friends', 'friend_id')
                        ))
                )),
            ['posts-count' => 'posts-count', 'friends-count' => 'friends-count'], []
        ));
    }

    public function testFlattenNestedToManyRelationThenCountInLoad()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'loadAll(postIds).flatten(comments).count()' => 'total-comments',
            ]);

        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addColumn('total-comments', Expr::subSelect(
                    Select::from($this->tables['posts'])
                        ->addColumn('__single_val', Expr::count())
                        ->join(Join::inner($this->tables['comments'], 'comments', [
                            Expr::equal(
                                $this->tableColumn('posts', 'id'),
                                $this->tableColumn('comments', 'post_id')
                            ),
                        ]))
                        ->where(Expr::equal(
                            $this->column('id'),
                            $this->tableColumn('posts', 'author_id')
                        ))
                )),
            ['total-comments' => 'total-comments'], []
        ));
    }

    public function testLoadOneToManyRelationAsObjects()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'loadAll(postIds)' => 'posts',
            ]);

        $objectMapper = $this->mapper->getMapper();
        /** @var ToManyRelation $relation */
        $relation = $objectMapper->getDefinition()->getRelationMappedToProperty('postIds');
        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addAliasedRawColumn('posts_id', 'id'),
            [],
            [
                'posts' => [
                    new ToManyMemberRelation(
                        (new ToManyRelationMapping($objectMapper, [], $relation->withObjectReference()))
                    ),
                    $this->tables['users'],
                    ['posts_id' => 'id'],
                ],
            ]
        ));
    }

    public function testLoadNestedToManyRelation()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'loadAll(postIds).flatten(comments)' => 'all-comments',
            ]);

        $objectMapper = $this->mapper->getMapper();
        /** @var ToManyRelation $postRelation */
        /** @var ToManyRelation $commentRelation */
        $postRelation    = $objectMapper->getDefinition()->getRelationMappedToProperty('postIds');
        $commentRelation = $postRelation->getEntityMapper()->getDefinition()->getRelationMappedToProperty('comments');
        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addAliasedRawColumn('all-comments_id', 'id'),
            [],
            [
                'all-comments' => [
                    new ToManyMemberRelation(
                        (new ToManyRelationMapping($objectMapper, [$postRelation->withObjectReference()], $commentRelation))
                    ),
                    $this->tables['users'],
                    ['all-comments_id' => 'id'],
                ],
            ]
        ));
    }

    public function testMultipleToOneRelationReferencesResultsInASingleJoin()
    {
        $criteria = $this->loadMapper->newCriteria()
            ->loadAll([
                'alias.firstName' => 'alias-first-name',
                'alias.lastName'  => 'alias-last-name',
            ])
            ->whereStringContains('alias.firstName', 'a')
            ->orderByAsc('alias.firstName')
            ->orderByAsc('alias.lastName');

        $this->assertMappedLoadQuery($criteria, new MappedLoadQuery(
            $this->select()
                ->addColumn('alias-first-name', $this->tableColumn('aliases', 'first_name'))
                ->addColumn('alias-last-name', $this->tableColumn('aliases', 'last_name'))
                ->join(Join::left($this->tables['aliases'], 'aliases', [
                    Expr::equal(
                        $this->tableColumn('users', 'id'),
                        $this->tableColumn('aliases', 'user_id')
                    ),
                ]))
                ->where(Expr::strContains(
                    $this->tableColumn('aliases', 'first_name'),
                    Expr::param(null, 'a')
                ))
                ->orderByAsc($this->tableColumn('aliases', 'first_name'))
                ->orderByAsc($this->tableColumn('aliases', 'last_name')),
            ['alias-first-name' => 'alias-first-name', 'alias-last-name' => 'alias-last-name'], []
        ));
    }
}