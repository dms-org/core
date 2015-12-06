<?php

namespace Iddigital\Cms\Core\Model\Criteria;

use Iddigital\Cms\Core\Exception\BaseException;
use Iddigital\Cms\Core\Model\Criteria\Member\CollectionCountMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\LoadAllIdsMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\LoadIdMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\MemberPropertyExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\ObjectSetAverageMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\ObjectSetFlattenMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\ObjectSetMaximumMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\ObjectSetMinimumMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\ObjectSetSumMethodExpression;
use Iddigital\Cms\Core\Model\Criteria\Member\SelfExpression;
use Iddigital\Cms\Core\Model\IEntity;
use Iddigital\Cms\Core\Model\Object\FinalizedClassDefinition;
use Iddigital\Cms\Core\Model\Object\TypedObject;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\CollectionType;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\ObjectType;
use Iddigital\Cms\Core\Model\Type\WithElementsType;

/**
 * The member expression parser class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberExpressionParser implements IMemberExpressionParser
{
    const TYPE_PROPERTY = 'property';
    const TYPE_METHOD = 'method';

    /**
     * @var IEntitySetProvider|null
     */
    protected $entitySetProvider;

    /**
     * @var IRelationPropertyIdTypeProvider|null
     */
    protected $relationPropertyIdTypeProvider;

    /**
     * MemberExpressionParser constructor.
     *
     * @param IEntitySetProvider|null              $entitySetProvider
     * @param IRelationPropertyIdTypeProvider|null $relationPropertyIdTypeProvider
     */
    public function __construct(
            IEntitySetProvider $entitySetProvider = null,
            IRelationPropertyIdTypeProvider $relationPropertyIdTypeProvider = null
    ) {
        $this->entitySetProvider              = $entitySetProvider;
        $this->relationPropertyIdTypeProvider = $relationPropertyIdTypeProvider;
    }

    /**
     * @inheritDoc
     */
    public function parse(FinalizedClassDefinition $rootCollectionType, $string)
    {
        try {
            $parts = $this->parseIntoParts($string);

            return $this->parsePartsIntoExpressionObject(Type::object($rootCollectionType->getClassName()), $parts);
        } catch (BaseException $inner) {
            if ($inner instanceof InvalidMemberExpressionException && $inner->getPrevious()) {
                $inner = $inner->getPrevious();
            }

            throw new InvalidMemberExpressionException(
                    sprintf(
                            'Could not parse member expression string \'%s\' from type %s: %s',
                            $string, $rootCollectionType->getClassName(), $inner->getMessage()
                    ),
                    null,
                    $inner
            );
        }
    }

    /**
     * Parses the expression string into an array format.
     *
     * Example:
     * <code>
     * 'some.method(param, abc).foo'
     * </code>
     * Becomes:
     * <code>
     * [
     *      [self::TYPE_PROPERTY, 'some', []],
     *      [self::TYPE_METHOD, 'method', ['param', 'abc']],
     *      [self::TYPE_PROPERTY, 'foo'],
     * ]
     * </code>
     *
     * @param string $string
     *
     * @return array
     * @throws BaseException
     */
    private function parseIntoParts($string)
    {
        $parts            = [];
        $currentType      = self::TYPE_PROPERTY;
        $currentName      = '';
        $currentParam     = '';
        $currentParams    = [];
        $parenthesisCount = 0;

        foreach (str_split($string) as $key => $char) {

            if ($char === '(') {
                if (!$currentName) {
                    throw BaseException::format('unexpected \'(\', must be following method name');
                }

                if ($currentParam) {
                    $currentParam .= '(';
                }

                $parenthesisCount++;
                $currentType = self::TYPE_METHOD;
            } //
            elseif ($char === ',' && $parenthesisCount <= 1) {
                if ($parenthesisCount === 0) {
                    throw BaseException::format('unexpected \',\', must be within parenthesis');
                }

                $currentParams[] = trim($currentParam);
                $currentParam    = '';
            } //
            elseif ($char === ')') {
                if ($parenthesisCount === 0) {
                    throw BaseException::format('unexpected \')\', does not match opening parenthesis');
                }

                if ($parenthesisCount === 1) {
                    if ($currentParam) {
                        $currentParams[] = trim($currentParam);
                    }
                } else {
                    $currentParam .= ')';
                }

                $parenthesisCount--;
            } //
            elseif ($char === '.' && $parenthesisCount === 0) {
                $parts[] = [
                        $currentType,
                        $currentName,
                        $currentParams
                ];

                $currentType   = self::TYPE_PROPERTY;
                $currentName   = '';
                $currentParam  = '';
                $currentParams = [];
            } //
            else {
                if ($parenthesisCount === 0) {
                    $currentName .= $char;
                } else {
                    $currentParam .= $char;
                }
            }
        }

        if ($parenthesisCount > 0) {
            throw BaseException::format('unexpected end of string, unbalanced \'(\' opening parenthesis');
        }

        if (!isset($char) || $char === '.') {
            throw BaseException::format('unexpected end of string, member name cannot be empty');
        }

        if ($currentName) {
            $parts[] = [
                    $currentType,
                    $currentName,
                    $currentParams
            ];
        }

        return $parts;
    }

    protected function parsePartsIntoExpressionObject(IType $sourceType, array $parts)
    {
        $expressions = [];

        foreach ($parts as list($type, $name, $params)) {
            if ($type === self::TYPE_PROPERTY) {
                $expression = $this->parsePropertyExpression($sourceType, $name);
            } else {
                $expression = $this->parseMethodExpression($sourceType, $name, $params);
            }

            $sourceType    = $expression->getResultingType();
            $expressions[] = $expression;
        }

        foreach ($expressions as $key => $expression) {
            if ($expression instanceof SelfExpression && count ($expressions) > 1) {
                unset($expressions[$key]);
            }
        }

        return new NestedMember(array_values($expressions));
    }

    private function parsePropertyExpression(IType $sourceType, $propertyName)
    {
        $definition = $this->assertSourceIsTypedObject('get property', $propertyName, $sourceType);

        if ($propertyName === SelfExpression::IDENTIFIER) {
            return new SelfExpression($sourceType);
        }

        return new MemberPropertyExpression(
                $definition->getProperty($propertyName),
                $sourceType->isNullable()
        );
    }

    private function parseMethodExpression(IType $sourceType, $methodName, array $params)
    {
        switch ($methodName) {
            case LoadIdMethodExpression::METHOD_NAME:
            case LoadAllIdsMethodExpression::METHOD_NAME:
                return $this->parseLoadExpression($sourceType, $methodName, $params);

            case CollectionCountMethodExpression::METHOD_NAME:
                return $this->parseCountExpression($sourceType, $methodName, $params);

            case ObjectSetFlattenMethodExpression::METHOD_NAME:
                return new ObjectSetFlattenMethodExpression(
                        $sourceType,
                        $this->assertCollectionSourceAndSingleMemberParameter($sourceType, $methodName, $params)
                );

            case ObjectSetAverageMethodExpression::METHOD_NAME:
                return new ObjectSetAverageMethodExpression(
                        $sourceType,
                        $this->assertCollectionSourceAndSingleMemberParameter($sourceType, $methodName, $params)
                );

            case ObjectSetMaximumMethodExpression::METHOD_NAME:
                return new ObjectSetMaximumMethodExpression(
                        $sourceType,
                        $this->assertCollectionSourceAndSingleMemberParameter($sourceType, $methodName, $params)
                );

            case ObjectSetMinimumMethodExpression::METHOD_NAME:
                return new ObjectSetMinimumMethodExpression(
                        $sourceType,
                        $this->assertCollectionSourceAndSingleMemberParameter($sourceType, $methodName, $params)
                );

            case ObjectSetSumMethodExpression::METHOD_NAME:
                return new ObjectSetSumMethodExpression(
                        $sourceType,
                        $this->assertCollectionSourceAndSingleMemberParameter($sourceType, $methodName, $params)
                );

            default:
                throw BaseException::format('call to unknown method name \'%s\'', $methodName);
        }
    }

    private function assertHasEntityProvider($methodName)
    {
        if (!$this->entitySetProvider) {
            throw BaseException::format(
                    'cannot call method \'%s\', entity set provider is not available',
                    $methodName
            );
        }
    }

    private function assertParamsCount($methodName, array $params, $expected)
    {
        if (count($params) !== $expected) {
            throw BaseException::format(
                    'call to method \'%s\' expects %d parameters, %d given',
                    $methodName, $expected, count($params)
            );
        }
    }

    protected function assertSourceIsTypedObject($action, $name, IType $sourceType)
    {
        $sourceType = $sourceType->nonNullable();

        if (!($sourceType instanceof ObjectType) || !$sourceType->isSubsetOf(TypedObject::type())) {
            throw BaseException::format(
                    'cannot %s \'%s\', type must be instance of %s, %s given',
                    $action, $name, TypedObject::class, $sourceType->asTypeString()
            );
        }

        /** @var string|TypedObject $objectType */
        $objectType = $sourceType->getClass();

        return $objectType::definition();
    }

    protected function assertSourceIsCollectionOfTypedObjects($action, $name, IType $sourceType)
    {
        $sourceType = $sourceType->nonNullable();

        if (!($sourceType instanceof CollectionType) || !$sourceType->getElementType()->nonNullable()->isSubsetOf(TypedObject::type())) {
            throw BaseException::format(
                    'cannot %s \'%s\' on type %s, source type must be a %s',
                    $action, $name, $sourceType->asTypeString(), Type::collectionOf(TypedObject::type())->asTypeString()
            );
        }

        /** @var ObjectType $elementsType */
        /** @var string|TypedObject $objectType */
        $elementsType = $sourceType->getElementType()->nonNullable();
        $objectType   = $elementsType->getClass();

        return $objectType::definition();
    }

    protected function assertCollectionSourceAndSingleMemberParameter(IType $sourceType, $methodName, array $params)
    {
        $this->assertParamsCount($methodName, $params, 1);
        $definition = $this->assertSourceIsCollectionOfTypedObjects('call method', $methodName, $sourceType);

        return $this->parse($definition, $params[0]);
    }

    private function parseLoadExpression(IType $sourceType, $methodName, array $params)
    {
        // param 1 = id member expression
        // param 2 = type of entity which id refers to, optional

        $this->assertHasEntityProvider($methodName);

        $definition = $this->assertSourceIsTypedObject('call method', $methodName, $sourceType->nonNullable());

        if (count($params) === 0 || count($params) > 2) {
            throw BaseException::format(
                    'call to method \'%s\' expects 1 or 2 parameters, %d given',
                    $methodName, count($params)
            );
        }

        $idMemberExpression = $this->parse($definition, $params[0]);

        if (count($params) === 2) {
            if (!is_subclass_of($params[1], IEntity::class, true)) {
                throw BaseException::format(
                        'call to method \'%s\' expects 2nd parameter to be a subclass of %s, %s given',
                        $methodName, IEntity::class, $params[1]
                );
            }

            $relatedEntityType = $params[1];
        } else {
            if (!$this->relationPropertyIdTypeProvider) {
                throw BaseException::format(
                        'call to method \'%s\' cannot infer related entity type of <%s>.%s, either set related id type provider or pass the entity type as 2nd parameter',
                        $methodName, $definition->getClassName(), $params[0]
                );
            }

            $idProperty = $idMemberExpression->getProperty();

            if (!$idProperty) {
                throw BaseException::format(
                        'call to method \'%s\' cannot infer related entity type of <%s>.%s, must be a property value',
                        $methodName, $definition->getClassName(), $params[0]
                );
            }

            $relatedEntityType = $this->relationPropertyIdTypeProvider->loadRelatedEntityType(
                    $idMemberExpression->getLastPart()->getSourceType()->asTypeString(),
                    $idProperty->getName()
            );
        }

        $dataSource = $this->entitySetProvider->loadDataSourceFor($relatedEntityType);

        if ($methodName === LoadIdMethodExpression::METHOD_NAME) {
            return new LoadIdMethodExpression(
                    $sourceType,
                    $idMemberExpression,
                    $dataSource
            );
        } else {
            return new LoadAllIdsMethodExpression(
                    $sourceType,
                    $idMemberExpression,
                    $dataSource
            );
        }
    }

    private function parseCountExpression(IType $sourceType, $methodName, array $params)
    {
        $this->assertParamsCount($methodName, $params, 0);
        $instanceSourceType = $sourceType->nonNullable();

        if (!($instanceSourceType instanceof WithElementsType)) {
            throw BaseException::format(
                    'cannot call method \'%s\' on type %s, source type must be a %s',
                    $methodName, $sourceType->asTypeString(),
                    Type::collectionOf(Type::mixed())->union(Type::arrayOf(Type::mixed()))->asTypeString()
            );
        }

        return new CollectionCountMethodExpression($sourceType);
    }
}