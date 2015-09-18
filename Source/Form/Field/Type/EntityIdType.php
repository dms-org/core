<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Options\EntityIdOptions;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\IntValidator;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;
use Iddigital\Cms\Core\Model\Type\IType as IPhpType;

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