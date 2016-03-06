<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Options\ObjectIdentityOptions;
use Dms\Core\Form\Field\Processor\CustomProcessor;
use Dms\Core\Form\Field\Processor\ObjectIdProcessor;
use Dms\Core\Form\Field\Processor\ObjectLoaderProcessor;
use Dms\Core\Form\Field\Processor\Validator\ObjectIdValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Language\Message;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;

/**
 * The object id type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectIdType extends FieldType
{
    /**
     * @var IIdentifiableObjectSet
     */
    private $objects;

    /**
     * @var bool
     */
    private $loadAsObjects;

    /**
     * ObjectIdType constructor.
     *
     * @param ObjectIdentityOptions $options
     * @param bool                  $loadAsObjects
     */
    public function __construct(ObjectIdentityOptions $options, bool $loadAsObjects = false)
    {
        $this->objects                        = $options->getObjects();
        $this->attributes[self::ATTR_OPTIONS] = $options;
        $this->loadAsObjects                  = $loadAsObjects;
        parent::__construct();
    }

    /**
     * @return IIdentifiableObjectSet
     */
    public function getObjects() : IIdentifiableObjectSet
    {
        return $this->objects;
    }

    /**
     * @return IPhpType
     */
    protected function buildPhpTypeOfInput() : IPhpType
    {
        return Type::mixed();
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        $processors = [
                new ObjectIdProcessor(Type::mixed()),
                new ObjectIdValidator(Type::string()->union(Type::int())->nullable(), $this->objects),
        ];

        if ($this->loadAsObjects) {
            $processors[] = new ObjectLoaderProcessor($this->objects);
        }

        return $processors;
    }
}