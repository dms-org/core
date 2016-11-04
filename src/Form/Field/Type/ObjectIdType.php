<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Options\ObjectIdentityOptions;
use Dms\Core\Form\Field\Processor\EmptyStringToNullProcessor;
use Dms\Core\Form\Field\Processor\ObjectIdProcessor;
use Dms\Core\Form\Field\Processor\ObjectLoaderProcessor;
use Dms\Core\Form\Field\Processor\Validator\ObjectIdValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;
use Dms\Core\Persistence\IRepository;

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
     * @return bool
     */
    protected function hasTypeSpecificOptionsValidator() : bool
    {
        return true;
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        if ($this->objects instanceof IRepository) {
            $inputType = Type::int()->nullable();
        } else {
            $inputType = Type::string()->union(Type::int())->nullable();
        }

        $processors = [
            new EmptyStringToNullProcessor(Type::mixed()),
            new ObjectIdProcessor(Type::mixed()),
            new ObjectIdValidator($inputType, $this->objects),
        ];

        if ($this->loadAsObjects) {
            $processors[] = new ObjectLoaderProcessor($this->objects);
        }

        return $processors;
    }
}