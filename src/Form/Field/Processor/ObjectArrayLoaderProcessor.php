<?php declare(strict_types=1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Model\IEntity;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IIdentifiableObjectSet;
use Dms\Core\Model\IObjectSetWithLoadCriteriaSupport;
use Dms\Core\Model\ITypedObject;
use Dms\Core\Model\Object\Entity;
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
        $idToKeyMap = array_flip($input);

        $objects                 = $this->objects->getAllById($input);
        $objectsWithOriginalKeys = [];

        foreach ($objects as $object) {
            $objectsWithOriginalKeys[$idToKeyMap[$this->objects->getObjectId($object)]] = $object;
        }

        return $objectsWithOriginalKeys;
    }

    protected function doUnprocess($input)
    {
        /** @var ITypedObject[] $input */

        $ids = [];

        $dataSource = $this->objects;
        foreach ($input as $object) {
            $ids[] = $object instanceof IEntity ? $object->getId() : $dataSource->getObjectId($object);
        }

        if (empty($ids)) {
            return [];
        }

        if ($dataSource instanceof IEntitySet && $dataSource instanceof IObjectSetWithLoadCriteriaSupport) {
            $validIds = array_column($dataSource->loadMatching(
                $dataSource->loadCriteria()
                    ->load(Entity::ID, 'id')
                    ->whereIn(Entity::ID, $ids)
            ), 'id');
        } else {
            $validIds = array_map(
                function ($object) use ($dataSource) {
                    return $dataSource->getObjectId($object);
                },
                $dataSource->tryGetAll($ids)
            );
        }

        return $validIds;
    }
}