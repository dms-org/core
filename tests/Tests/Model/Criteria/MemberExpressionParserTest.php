<?php

namespace Dms\Core\Tests\Model\Criteria;

use Dms\Common\Testing\CmsTestCase;
use Dms\Core\Model\Criteria\IEntitySetProvider;
use Dms\Core\Model\Criteria\InvalidMemberExpressionException;
use Dms\Core\Model\Criteria\IRelationPropertyIdTypeProvider;
use Dms\Core\Model\Criteria\Member\CollectionCountMethodExpression;
use Dms\Core\Model\Criteria\Member\LoadAllIdsMethodExpression;
use Dms\Core\Model\Criteria\Member\LoadIdMethodExpression;
use Dms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetAverageMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetFlattenMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetMaximumMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetMinimumMethodExpression;
use Dms\Core\Model\Criteria\Member\ObjectSetSumMethodExpression;
use Dms\Core\Model\Criteria\Member\SelfExpression;
use Dms\Core\Model\Criteria\MemberExpressionParser;
use Dms\Core\Model\Criteria\NestedMember;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Tests\Model\Criteria\Fixtures\ParentEntity;
use Dms\Core\Tests\Model\Criteria\Fixtures\RelatedEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionParserTest extends CmsTestCase
{
    protected function getTestRootDefinition()
    {
        return ParentEntity::definition();
    }

    public function errorCases()
    {
        return [
            //
            // Syntax
            ['unbalanced('],
            ['unbalanced(abc))'],
            ['invalid.(foo)'],
            ['a.b.'],
            ['.'],
            //
            // Non existent
            ['data.foo()'],
            ['nonExistentProperty'],
            ['non.existent.property'],
            ['nonExistentMethod()'],
            ['non.existent.method()'],
            ['nonExistentMethod(with, params)'],
            ['non.existent.method(with, params)'],
            ['relatedEntity.prop.abc'],
            //
            // Loads -- invalid since no entity set provider given
            ['load(relatedId)'],
            ['load(relatedId, Some\Invalid\Class)'],
            ['loadAll(relatedIds)'],
            ['loadAll(relatedIds, Some\Invalid\Class)'],
            ['load(too,many,params)'],
            ['loadAll(too,many,params)'],
            //
            // Collection methods
            ['relatedIds.count(abc)'], // too many params
            ['relatedEntities.sum()'], // too few params
            ['relatedEntities.average(number, abc)'], // too many params
            ['relatedEntities.min(number, abc)'], // too many params
            ['relatedEntities.max(number, abc)'], // too many params
            ['relatedEntities.flatten(numbers, abc)'], // too many params
            ['relatedEntities.flatten(prop)'], // not a collection
        ];
    }

    public function successCases()
    {
        $parent  = ParentEntity::definition();
        $related = RelatedEntity::definition();

        return [
            // Self
            [
                    'this',
                    [
                            new SelfExpression(Type::object($parent->getClassName()))
                    ]
            ],
            [
                    // Should remove useless self expression
                    'this.this.this',
                    [
                            new SelfExpression(Type::object($parent->getClassName()))
                    ]
            ],
            // Properties
            [
                    'data',
                    [
                            new MemberPropertyExpression($parent->getProperty('data'), false)
                    ]
            ],
            [
                    'relatedEntity.prop',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntity'), false),
                            new MemberPropertyExpression($related->getProperty('prop'), true),
                    ]
            ],
            [
                    // Should remove useless self expression
                    'relatedEntity.this.prop',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntity'), false),
                            new MemberPropertyExpression($related->getProperty('prop'), true),
                    ]
            ],
            // Methods
            [
                    'relatedIds.count()',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedIds'), false),
                            new CollectionCountMethodExpression($parent->getProperty('relatedIds')->getType()),
                    ]
            ],
            [
                    'relatedEntities.sum(number)',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntities'), false),
                            new ObjectSetSumMethodExpression($parent->getProperty('relatedEntities')->getType(), new NestedMember([
                                    new MemberPropertyExpression($related->getProperty('number'), false)
                            ]))
                    ]
            ],
            [
                    'relatedEntities.average(number)',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntities'), false),
                            new ObjectSetAverageMethodExpression($parent->getProperty('relatedEntities')->getType(), new NestedMember([
                                    new MemberPropertyExpression($related->getProperty('number'), false)
                            ]))
                    ]
            ],
            [
                    'relatedEntities.max(number)',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntities'), false),
                            new ObjectSetMaximumMethodExpression($parent->getProperty('relatedEntities')->getType(), new NestedMember([
                                    new MemberPropertyExpression($related->getProperty('number'), false)
                            ]))
                    ]
            ],
            [
                    'relatedEntities.min(number)',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntities'), false),
                            new ObjectSetMinimumMethodExpression($parent->getProperty('relatedEntities')->getType(), new NestedMember([
                                    new MemberPropertyExpression($related->getProperty('number'), false)
                            ]))
                    ]
            ],
            [
                    'relatedEntities.flatten(numbers)',
                    [
                            new MemberPropertyExpression($parent->getProperty('relatedEntities'), false),
                            new ObjectSetFlattenMethodExpression($parent->getProperty('relatedEntities')->getType(), new NestedMember([
                                    new MemberPropertyExpression($related->getProperty('numbers'), false)
                            ]))
                    ]
            ],
        ];
    }

    /**
     * @dataProvider errorCases
     *
     * @param $string
     *
     * @return void
     */
    public function testParsingInvalidMemberString($string)
    {
        $this->expectException(InvalidMemberExpressionException::class);

        $parser = new MemberExpressionParser();

        $parser->parse($this->getTestRootDefinition(), $string);
    }

    /**
     * @dataProvider successCases
     *
     * @param       $string
     * @param array $expectedMemberParts
     */
    public function testParsingValidMemberString($string, array $expectedMemberParts)
    {
        $parser = new MemberExpressionParser();

        $results = $parser->parse($this->getTestRootDefinition(), $string);

        $this->assertSame(strtr($string, [', ' => ',', '.this' => '']), $results->asString());
        $this->assertEquals($expectedMemberParts, $results->getParts());
    }

    public function testLoadWithExplicitTypeProperty()
    {
        $entitySetProviderMock = $this->getMockForAbstractClass(IEntitySetProvider::class);

        $entitySetProviderMock->method('loadDataSourceFor')
                ->with(RelatedEntity::class)
                ->willReturn($dataSource = RelatedEntity::collection([new RelatedEntity('abc', 1, [1, 2, 3])]));

        $parser  = new MemberExpressionParser($entitySetProviderMock);
        $results = $parser->parse(ParentEntity::definition(), 'load(relatedId, ' . RelatedEntity::class . ')');

        $this->assertEquals([
                new LoadIdMethodExpression(
                        ParentEntity::type(),
                        new NestedMember([
                                new MemberPropertyExpression(ParentEntity::definition()->getProperty('relatedId'), false)
                        ]),
                        $dataSource
                )
        ], $results->getParts());

        $results = $parser->parse(ParentEntity::definition(), 'loadAll(relatedIds, ' . RelatedEntity::class . ')');

        $this->assertEquals([
                new LoadAllIdsMethodExpression(
                        ParentEntity::type(),
                        new NestedMember([
                                new MemberPropertyExpression(ParentEntity::definition()->getProperty('relatedIds'), false)
                        ]),
                        $dataSource
                )
        ], $results->getParts());
    }

    public function testLoadWithImplicitRelatedEntityType()
    {
        $entitySetProviderMock = $this->getMockForAbstractClass(IEntitySetProvider::class);

        $entitySetProviderMock->method('loadDataSourceFor')
                ->with(RelatedEntity::class)
                ->willReturn($dataSource = RelatedEntity::collection([new RelatedEntity('abc', 1, [1, 2, 3])]));

        $relatedIdTypeProviderMock = $this->getMockForAbstractClass(IRelationPropertyIdTypeProvider::class);

        $relatedIdTypeProviderMock->method('loadRelatedEntityType')
                ->withConsecutive(
                        [ParentEntity::class, [], 'relatedId'],
                        [ParentEntity::class, [], 'relatedIds']
                )
                ->willReturn(RelatedEntity::class);

        $parser  = new MemberExpressionParser($entitySetProviderMock, $relatedIdTypeProviderMock);

        $results = $parser->parse(ParentEntity::definition(), 'load(relatedId)');

        $this->assertEquals([
                new LoadIdMethodExpression(
                        ParentEntity::type(),
                        new NestedMember([
                                new MemberPropertyExpression(ParentEntity::definition()->getProperty('relatedId'), false)
                        ]),
                        $dataSource
                )
        ], $results->getParts());

        $results = $parser->parse(ParentEntity::definition(), 'loadAll(relatedIds)');

        $this->assertEquals([
                new LoadAllIdsMethodExpression(
                        ParentEntity::type(),
                        new NestedMember([
                                new MemberPropertyExpression(ParentEntity::definition()->getProperty('relatedIds'), false)
                        ]),
                        $dataSource
                )
        ], $results->getParts());
    }
}