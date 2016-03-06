<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Model\EntityCollection;
use Dms\Core\Model\IMutableObjectSet;
use Dms\Core\Model\ITypedObjectCollection;
use Dms\Core\Model\Object\ValueObject;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The inner crud module value type processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerCrudModuleProcessor extends FieldProcessor
{
    /**
     * @var ICrudModule
     */
    private $module;

    public function __construct(ICrudModule $module)
    {
        parent::__construct(Type::from($module->getDataSource()));

        $this->module = $module;
    }

    protected function doProcess($input, array &$messages)
    {
        $dataSource      = $this->module->getDataSource();
        $originalObjects = $dataSource->getAll();
        $objectsToRemove = [];
        $newObjects      = [];

        $currentPosition = 1;
        foreach ($input as $item) {
            if (isset($item[IObjectAction::OBJECT_FIELD_NAME])) {
                $editedObject = $this->module->getEditAction()->run($item);
                $newObjects[] = $editedObject;

                if ($dataSource instanceof ITypedObjectCollection) {
                    $dataSource->move($editedObject, $currentPosition);
                }
            } else {
                $newObjects[] = $this->module->getCreateAction()->run($item);
            }

            $currentPosition++;
        }

        foreach ($originalObjects as $originalObject) {
            if (!in_array($originalObject, $newObjects, true)) {
                $objectsToRemove[] = $originalObject;
            }
        }

        $dataSource->removeAll($objectsToRemove);

        return $dataSource;
    }

    protected function doUnprocess($input)
    {
        $stagedForm         = $this->module->getEditAction()->getStagedForm();
        $unprocessedObjects = [];

        /** @var IMutableObjectSet $input */
        foreach ($input->getAll() as $object) {
            $stages = $stagedForm->withSubmittedFirstStage([IObjectAction::OBJECT_FIELD_NAME => $object]);
            $objectId = $this->module->getDataSource()->getObjectId($object);

            if (strpos((string)$objectId, EntityCollection::ENTITY_WITHOUT_ID_PREFIX) === 0 || $object instanceof ValueObject) {
                $objectData = [];
            } else {
                $objectData = [IObjectAction::OBJECT_FIELD_NAME => $objectId];
            }

            foreach ($stages->getAllStages() as $stage) {
                $currentStageForm = $stage->loadForm($objectData);

                $objectData += $currentStageForm->unprocess($currentStageForm->getInitialValues());
            }

            $unprocessedObjects[] = $objectData;
        }

        return $unprocessedObjects;
    }
}