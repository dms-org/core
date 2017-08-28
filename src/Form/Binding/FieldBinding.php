<?php declare(strict_types=1);

namespace Dms\Core\Form\Binding;

use Dms\Core\Form\Binding\Accessor\IFieldAccessor;
use Dms\Core\Form\IField;

/**
 * The field binding class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldBinding implements IFieldBinding
{
    /**
     * @var IField
     */
    protected $fieldName;


    /**
     * @var IFieldAccessor
     */
    protected $accessor;

    /**
     * FieldBinding constructor.
     *
     * @param string         $fieldName
     * @param IFieldAccessor $accessor
     */
    public function __construct(string $fieldName, IFieldAccessor $accessor)
    {
        $this->fieldName  = $fieldName;
        $this->accessor   = $accessor;
    }

    /**
     * @inheritDoc
     */
    final public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @inheritDoc
     */
    final public function getObjectType(): string
    {
        return $this->accessor->getObjectType();
    }

    /**
     * Gets the field accessor
     *
     * @return IFieldAccessor
     */
    public function getAccessor(): IFieldAccessor
    {
        return $this->accessor;
    }
}