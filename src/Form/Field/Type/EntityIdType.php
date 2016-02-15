<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Options\EntityIdOptions;
use Dms\Core\Form\Field\Processor\EntityLoaderProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\EntityIdValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;

/**
 * The entity id type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class EntityIdType extends FieldType
{
    /**
     * @var IEntitySet
     */
    private $entities;

    /**
     * @var bool
     */
    private $loadAsObjects;

    /**
     * EntityIdType constructor.
     *
     * @param IEntitySet $entities
     * @param bool       $loadAsObjects
     */
    public function __construct(IEntitySet $entities, bool $loadAsObjects = false)
    {
        $this->entities                       = $entities;
        $this->attributes[self::ATTR_OPTIONS] = new EntityIdOptions($entities);
        $this->loadAsObjects                  = $loadAsObjects;
        parent::__construct();
    }

    /**
     * @return IPhpType
     */
    protected function buildPhpTypeOfInput() : \Dms\Core\Model\Type\IType
    {
        return Type::mixed();
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        $processors = [
                new IntValidator($this->inputType),
                new TypeProcessor('int'),
                new EntityIdValidator(Type::int()->nullable(), $this->entities),
        ];

        if ($this->loadAsObjects) {
            $processors[] = new EntityLoaderProcessor($this->entities);
        }

        return $processors;
    }
}