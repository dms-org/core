<?php

namespace Iddigital\Cms\Core\Model\Criteria\Member;

use Iddigital\Cms\Core\Exception\NotImplementedException;
use Iddigital\Cms\Core\Model\Criteria\IMemberExpression;
use Iddigital\Cms\Core\Model\Type\IType;
use Iddigital\Cms\Core\Model\Type\ObjectType;

/**
 * The member method expression class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MemberMethodExpression implements IMemberExpression
{
    /**
     * @var IType
     */
    protected $sourceType;

    /**
     * @var bool
     */
    protected $isSourceNullable;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $arguments;

    /**
     * @var IType
     */
    protected $returnType;

    /**
     * MemberMethodExpression constructor.
     *
     * @param IType     $sourceType
     * @param string    $name
     * @param \string[] $arguments
     * @param IType     $returnType
     */
    public function __construct(IType $sourceType, $name, array $arguments, IType $returnType)
    {
        $this->sourceType       = $sourceType;
        $this->isSourceNullable = $sourceType->isNullable();
        $this->name             = $name;
        $this->arguments        = $arguments;
        $this->returnType       = $returnType;
    }

    /**
     * @inheritDoc
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return IType
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @inheritDoc
     */
    public function asString()
    {
        return $this->name . '(' . implode(',', $this->arguments) . ')';
    }

    /**
     * @inheritDoc
     */
    public function getResultingType()
    {
        return $this->isSourceNullable
                ? $this->returnType->nullable()
                : $this->returnType;
    }


    /**
     * Returns a callable that takes a parameter of the
     * source
     *
     * @return callable
     * @throws NotImplementedException
     */
    public function createGetterCallable()
    {
        $source = $this->sourceType->nonNullable();

        if ($source instanceof ObjectType && method_exists($source->getClass(), $this->name)) {
            $name      = $this->name;
            $arguments = $this->arguments;

            return function ($object) use ($name, $arguments) {
                return $object === null
                        ? null
                        : call_user_func_array([$object, $name], $arguments);
            };
        }

        throw NotImplementedException::format(
                'The method \'%s\' does not exist on type %s',
                $this->name, $source->asTypeString()
        );
    }
}