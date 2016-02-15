<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Object\FinalizedClassDefinition;

/**
 * The member expression parser.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IMemberExpressionParser
{
    /**
     * Parses a member expression string using dot '.' notation.
     *
     * Examples:
     * 'some.nested.property'
     * 'aCollection.count()'
     * 'another.collection.average(income)'
     *
     * @param FinalizedClassDefinition $rootCollectionType
     * @param string $string
     *
     * @return NestedMember
     * @throws InvalidArgumentException
     */
    public function parse(FinalizedClassDefinition $rootCollectionType, string $string) : NestedMember;
}