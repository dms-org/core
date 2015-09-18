<?php

namespace Iddigital\Cms\Core\Form\Field\Type;

use Iddigital\Cms\Core\Form\Field\Processor\ArrayAllProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\EntityArrayLoaderProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\TypeProcessor;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\EntityIdArrayValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\IntValidator;
use Iddigital\Cms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Iddigital\Cms\Core\Form\IField;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Type\Builder\Type;

/**
 * The array type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayOfEntityIdsType extends ArrayOfType
{
    const ATTR_ELEMENT_TYPE = 'element-type';
    const ATTR_MIN_ELEMENTS = 'min-elements';
    const ATTR_MAX_ELEMENTS = 'max-elements';

    /**
     * @var IEntitySet
     */
    private $entities;

    public function __construct(IEntitySet $entities, IField $entityIdField)
    {
        $this->entities = $entities;
        parent::__construct($entityIdField);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new ArrayAllProcessor([
                        new IntValidator($this->getElementType()->getPhpTypeOfInput()),
                        new TypeProcessor('int'),
                        new RequiredValidator(Type::int()->nullable())
                ]),
                new EntityIdArrayValidator(Type::arrayOf(Type::int())->nullable(), $this->entities),
        ];
    }
}