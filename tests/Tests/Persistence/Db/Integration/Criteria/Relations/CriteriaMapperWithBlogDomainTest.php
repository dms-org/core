<?php

namespace Dms\Core\Tests\Persistence\Db\Integration\Criteria;

use Dms\Core\Model\Criteria\LoadCriteria;
use Dms\Core\Model\Criteria\SpecificationDefinition;
use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Criteria\CriteriaMapper;
use Dms\Core\Persistence\Db\Query\Clause\Join;
use Dms\Core\Persistence\Db\Query\Expression\Expr;
use Dms\Core\Persistence\Db\Query\Select;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\Mapper\BlogOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestComment;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestPost;
use Dms\Core\Tests\Persistence\Db\Integration\Domains\Fixtures\Blog\TestUser;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CriteriaMapperWithBlogDomainTest extends CriteriaMapperTestBase
{
    protected function buildMapper()
    {
        return new CriteriaMapper(
            (new BlogOrm())->getEntityMapper(TestUser::class),
            $this->getMockForAbstractClass(IConnection::class)
        );
    }

    public function testMultipleLoadAllAggregatesInConditions()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('loadAll(postIds).count()', '=', 1)
            ->where('loadAll(friendIds).count()', '=', 2);

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::equal(
                    Expr::subSelect(
                        Select::from($this->tables['posts'])
                            ->addColumn('__single_val', Expr::count())
                            ->where(Expr::equal(
                                $this->column('id'),
                                $this->tableColumn('posts', 'author_id')
                            ))
                    ),
                    Expr::param(null, 1))
                )
                ->where(Expr::equal(
                    Expr::subSelect(
                        Select::from($this->tables['users'])
                            ->setAlias('users1')
                            ->addColumn('__single_val', Expr::count())
                            ->join(Join::inner($this->tables['user_friends'], 'user_friends', [
                                Expr::equal(
                                    $this->tableColumn('user_friends', 'user_id'),
                                    Expr::column('users1', $this->tables['users']->getColumn('id'))
                                )
                            ]))
                            ->where(Expr::equal(
                                $this->tableColumn('users', 'id'),
                                $this->tableColumn('user_friends', 'friend_id')
                            ))
                    ),
                    Expr::param(null, 2))
                )
        );
    }

    public function testFlattenNestedToManyRelationThenCount()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('loadAll(postIds).flatten(comments).count()', '=', 1);

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::equal(
                    Expr::subSelect(
                        Select::from($this->tables['posts'])
                            ->addColumn('__single_val', Expr::count())
                            ->join(Join::inner($this->tables['comments'], 'comments', [
                                Expr::equal(
                                    $this->tableColumn('posts', 'id'),
                                    $this->tableColumn('comments', 'post_id')
                                )
                            ]))
                            ->where(Expr::equal(
                                $this->column('id'),
                                $this->tableColumn('posts', 'author_id')
                            ))
                    ),
                    Expr::param(null, 1))
                )
        );
    }

    public function testIgnoresAnyLoadValuesForLoadCriteria()
    {
        $loadCriteria = new LoadCriteria(
            $this->mapper->getMapper()->getDefinition()->getClass(),
            $this->mapper->buildMemberExpressionParser()
        );

        $loadCriteria
            ->loadAll(['loadAll(postIds)', 'alias', 'password', 'firstName', 'lastName'])
            ->where('firstName', '=', 'foo');

        $this->assertMappedSelect($loadCriteria,
            $this->selectAllColumns()
                ->where(Expr::equal(
                    $this->column('first_name'),
                    Expr::param(null, 'foo'))
                )
        );

    }

    public function testNestedAggregateMethods()
    {
        $criteria = $this->mapper->newCriteria()
            ->where('loadAll(postIds).sum(comments.count())', '>', 1);

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::greaterThan(
                    Expr::subSelect(
                        Select::from($this->tables['posts'])
                            ->addColumn('__single_val', Expr::sum(
                                Expr::subSelect(
                                    Select::from($this->tables['comments'])
                                        ->addColumn('__single_val', Expr::count())
                                        ->where(Expr::equal(
                                            $this->tableColumn('posts', 'id'),
                                            $this->tableColumn('comments', 'post_id')
                                        ))
                                )
                            ))
                            ->where(Expr::equal(
                                $this->column('id'),
                                $this->tableColumn('posts', 'author_id')
                            ))
                    ),
                    Expr::param(null, 1))
                )
        );
    }

    public function testWhereHasAllOnNestedRelation()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereHasAll('loadAll(postIds).flatten(comments)', TestComment::specification(function (SpecificationDefinition $match) {
                $match->where('id', '>', 10);
            }));

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::subSelect(
                    Select::from($this->tables['posts'])
                        ->addColumn('__single_val', Expr::equal(Expr::count(), Expr::param(null, 0)))
                        ->join(Join::inner($this->tables['comments'], 'comments', [
                            Expr::equal(
                                $this->tableColumn('posts', 'id'),
                                $this->tableColumn('comments', 'post_id')
                            )
                        ]))
                        ->where(Expr::equal(
                            $this->column('id'),
                            $this->tableColumn('posts', 'author_id')
                        ))
                        ->where(Expr::not(Expr::greaterThan($this->tableColumn('comments', 'id'), Expr::param(null, 10))))
                ))
        );
    }

    public function testWhereHasAnyOnNestedRelation()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereHasAny('loadAll(postIds).flatten(comments)', TestComment::specification(function (SpecificationDefinition $match) {
                $match->where('id', '!=', 50);
            }));

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::subSelect(
                    Select::from($this->tables['posts'])
                        ->addColumn('__single_val', Expr::greaterThan(Expr::count(), Expr::param(null, 0)))
                        ->join(Join::inner($this->tables['comments'], 'comments', [
                            Expr::equal(
                                $this->tableColumn('posts', 'id'),
                                $this->tableColumn('comments', 'post_id')
                            )
                        ]))
                        ->where(Expr::equal(
                            $this->column('id'),
                            $this->tableColumn('posts', 'author_id')
                        ))
                        ->where(Expr::notEqual($this->tableColumn('comments', 'id'), Expr::param(null, 50)))
                ))
        );
    }

    public function testNestedWhereHas()
    {
        $criteria = $this->mapper->newCriteria()
            ->whereHasAll('loadAll(postIds)', TestPost::specification(function (SpecificationDefinition $match) {
                $match->where('id', '>', 10);

                $match->whereHasAny('comments', TestComment::specification(function (SpecificationDefinition $match) {
                    $match->where('id', '!=', 50);
                }));
            }));

        $this->assertMappedSelect($criteria,
            $this->selectAllColumns()
                ->where(Expr::subSelect(
                    Select::from($this->tables['posts'])
                        ->addColumn('__single_val', Expr::equal(Expr::count(), Expr::param(null, 0)))
                        ->where(Expr::equal(
                            $this->column('id'),
                            $this->tableColumn('posts', 'author_id')
                        ))
                        ->where(Expr::not(
                            Expr::and_(
                                Expr::greaterThan($this->tableColumn('posts', 'id'), Expr::param(null, 10)),
                                Expr::subSelect(
                                    Select::from($this->tables['comments'])
                                        ->addColumn('__single_val', Expr::greaterThan(Expr::count(), Expr::param(null, 0)))
                                        ->where(Expr::equal(
                                            $this->tableColumn('posts', 'id'),
                                            $this->tableColumn('comments', 'post_id')
                                        ))
                                        ->where(Expr::notEqual($this->tableColumn('comments', 'id'), Expr::param(null, 50)))
                                )
                            )
                        ))
                ))
        );
    }
}