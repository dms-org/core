<?php

namespace Iddigital\Cms\Core\Common\Crud\Form;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Form\Field\Builder\FieldNameBuilder;
use Iddigital\Cms\Core\Form\Object\FormObjectDefinition;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObject;
use Iddigital\Cms\Core\Form\Object\Stage\StagedFormObjectDefinition;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\IObjectSet;
use Iddigital\Cms\Core\Model\Object\ClassDefinition;
use Iddigital\Cms\Core\Model\Object\PropertyTypeDefiner;

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
     */
    public function __construct(IEntitySet $dataSource)
    {
        $this->dataSource = $dataSource;
        parent::__construct();
    }

    /**
     * Defines the structure of this class.
     *
     * @param ClassDefinition $class
     */
    final protected function defineClass(ClassDefinition $class)
    {
        $class->property($this->dataSource)->asObject(IObjectSet::class);

        $this->defineClassStructure($class->property($this->object), $class);
    }

    /**
     * Defines the structure of this class.
     *
     * @param PropertyTypeDefiner $object
     * @param ClassDefinition     $class
     *
     * @return void
     */
    abstract protected function defineClassStructure(PropertyTypeDefiner $object, ClassDefinition $class);

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
    public function getDataSource()
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