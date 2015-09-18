<?php

namespace Iddigital\Cms\Core\Model\Type;

use Iddigital\Cms\Core\Model\Type\Builder\Type;

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
    public function union(IType $type)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function nullable()
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function nonNullable()
    {
        return new NotType(Type::null());
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value)
    {
        return true;
    }
}