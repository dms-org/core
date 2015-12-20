<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;

/**
 * The array type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdType extends FieldType
{
    /**
     * @var IEntitySet
     */
    private $entities;

    public function __construct(IEntitySet $entities)
    {
        $this->entities                       = $entities;
        $this->attributes[self::ATTR_OPTIONS] = new EntityIdOptions($entities);
        parent::__construct();
    }

    /**
     * @return IPhpType
     */
    protected function buildPhpTypeOfInput()
    {
        return Type::mixed();
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new IntValidator($this->inputType),
                new TypeProcessor('int'),
                new EntityIdValidator(Type::int()->nullable(), $this->entities),
        ];
    }
}