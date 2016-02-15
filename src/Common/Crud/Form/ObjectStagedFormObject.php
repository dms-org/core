<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Form;

use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Form\Object\Stage\StagedFormObject;
use Dms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\IObjectSet;
use Dms\Core\Model\Object\ClassDefinition;

/**
 * The staged form object for a object action.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectStagedFormObject extends StagedFormObject
{
    /**
     * @var IEntitySet
     */
    protected $dataSource;

    /**
     * @var object
     */
    protected $object;

    /**
     * ObjectStagedFormObject constructor.
     *
     * @param IEntitySet $dataSource
     *
     * @throws TypeMismatchException
     */
    public function __construct(IEntitySet $dataSource)
    {
        $objectType       = $this->getObjectType();
        $this->dataSource = $dataSource;
        parent::__construct();

        if (!(is_a($dataSource->getObjectType(), $objectType, true))) {
            throw TypeMismatchException::format(
                    'Invalid data source supplied to %s: entity type must be compatible with %s, %s given',
                    get_class($this), $objectType, $dataSource->getObjectType()
            );
        }
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    final protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->object)->asObject($this->getObjectType());
        $class->property($this->dataSource)->asObject(IObjectSet::class);

        $this->defineClassStructure($class);
    }

    /**
     * Gets the expected type of object from the data source.
     *
     * @return string
     */
    abstract public function getObjectType() : string;

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     *
     * @return void
     */
    abstract protected function defineClassStructure(ClassDefinition $class);

    /**
     * Defines the staged form.
     *
     * @param StagedFormObjectDefinition $form
     */
    final protected function defineForm(StagedFormObjectDefinition $form)
    {
        $form->stage(function (FormObjectDefinition $form) {
            $form->section('Object', [
                    ObjectForm::objectField($form->field($this->object), $this->dataSource)
            ]);
        });

        $this->defineFormStages($form);
    }

    /**
     * Defines the following form stages.
     *
     * @param StagedFormObjectDefinition $form
     *
     * @return void
     */
    abstract protected function defineFormStages(StagedFormObjectDefinition $form);

    /**
     * @return IEntitySet
     */
    public function getDataSource() : \Dms\Core\Model\IEntitySet
    {
        return $this->dataSource;
    }

    /**
     * @return object
     */
    public function getObject()
    {
        return $this->object;
    }
}