<?php declare(strict_types = 1);

namespace Dms\Core\Model\Type;

use Dms\Core\Model\Type\Builder\Type;

/**
 * The mixed type class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class MixedType extends BaseType
{
    public function __construct()
    {
        parent::__construct('mixed');
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        return $type;
    }

    /**
     * {@inheritDoc}
     */
    public function union(IType $type) : IType
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function nullable() : IType
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function nonNullable() : IType
    {
        return new NotType(Type::null());
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value) : bool
    {
        return true;
    }
}