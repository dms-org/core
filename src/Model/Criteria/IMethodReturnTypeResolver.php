<?php declare(strict_types = 1);

namespace Dms\Core\Model\Criteria;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Model\Type\IType;

/**
 * The method class return resolver interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IMethodReturnTypeResolver
{
    /**
     * Loads the return type for the supplied member expression data.
     *
     * @param IType  $sourceType
     * @param string $name
     * @param array  $arguments
     *
     * @return IType
     * @throws InvalidArgumentException
     */
    public function loadReturnTypeFor(IType $sourceType, string $name, array $arguments) : \Dms\Core\Model\Type\IType;
}