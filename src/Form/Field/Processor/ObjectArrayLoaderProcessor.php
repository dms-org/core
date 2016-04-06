<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The entity loader validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectArrayLoaderProcessor extends FieldProcessor
{
    /**
     * @var IIdentifiableObjectSet
     */
    private $objects;

    public function __construct(IIdentifiableObjectSet $objects)
    {
        parent::__construct(Type::arrayOf($objects->getElementType()));

        $this->objects = $objects;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->objects->getAllById($input);
    }

    protected function doUnprocess($input)
    {
        /** @var ITypedObject[] $input */
        $ids = [];

        foreach ($input as $object) {
            if ($this->objects->contains($object)) {
                $ids[] = $this->objects->getObjectId($object);
            }
        }

        return $ids;
    }
}