<?php

namespace Dms\Core\Model\Type;

/**
 * The bot type class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class NotType extends BaseType
{
    /**
     * @var IType
     */
    private $type;

    public function __construct(IType $type)
    {
        $this->type = $type;

        parent::__construct('not<' . $type->asTypeString() . '>');
    }

    /**
     * @param IType $type
     *
     * @return IType|null
     */
    protected function intersection(IType $type)
    {
        if ($type instanceof self) {
            return new self(UnionType::create([$this->type, $type->type]));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function checkThisIsSubsetOf(IType $type)
    {
        if ($type instanceof self) {
            return $this->type->isSupersetOf($type->type);
        }

        return parent::checkThisIsSubsetOf($type);
    }


    /**
     * @return IType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function nonNullable()
    {
        return $this->type->isNullable() ? $this : new self($this->type->nullable());
    }

    /**
     * {@inheritDoc}
     */
    public function isOfType($value)
    {
        return !$this->type->isOfType($value);
    }
}