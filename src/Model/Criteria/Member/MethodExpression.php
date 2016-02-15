<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria\Member;

use Dms\Core\Model\Type\IType;

/**
 * The method expression base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class MethodExpression extends MemberExpression
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $arguments;

    /**
     * MemberMethodExpression constructor.
     *
     * @param IType    $sourceType
     * @param string   $name
     * @param string[] $arguments
     * @param IType    $returnType
     */
    public function __construct(IType $sourceType, string $name, array $arguments, IType $returnType)
    {
        $expressionString = $name . '(' . implode(',', $arguments) . ')';

        parent::__construct($sourceType, $returnType, $expressionString);
        $this->name      = $name;
        $this->arguments = $arguments;

        $this->verifyCompatibleWith($sourceType->nonNullable());
    }

    protected function verifyCompatibleWith(IType $source)
    {

    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
}