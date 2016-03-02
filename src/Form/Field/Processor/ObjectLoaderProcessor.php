<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\IIdentifiableObjectSet;

/**
 * The object loader validator.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectLoaderProcessor extends FieldProcessor
{
    /**
     * @var IIdentifiableObjectSet
     */
    private $objects;

    public function __construct(IIdentifiableObjectSet $objects)
    {
        parent::__construct($objects->getElementType());

        $this->objects = $objects;
    }

    protected function doProcess($input, array &$messages)
    {
        return $this->objects->get($input);
    }

    protected function doUnprocess($input)
    {
        return $this->objects->getObjectId($input);
    }
}