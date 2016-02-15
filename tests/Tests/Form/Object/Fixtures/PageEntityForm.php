<?php

namespace Dms\Core\Tests\Form\Object\Fixtures;

use Dms\Core\Form\Object\EntityFormObject;
use Dms\Core\Form\Object\FormObjectDefinition;
use Dms\Core\Model\IEntity;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PageEntityForm extends EntityFormObject
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $subTitle;

    /**
     * @var string
     */
    public $content;

    /**
     * Gets the type of entity of this form.
     *
     * @return string
     */
    protected function entityType() : string
    {
        return PageEntity::class;
    }

    /**
     * Defines the structure of the form object.
     *
     * @param FormObjectDefinition $form
     */
    protected function defineFormObject(FormObjectDefinition $form)
    {
        $form->section('Page Content', [
            //
            $form->field($this->title)->name('title')->label('Title')->string()->required()->maxLength(50),
            //
            $form->field($this->subTitle)->name('sub_title')->label('Sub Title')->string()->maxLength(50),
            //
            $form->field($this->content)->name('content')->label('Content')->string()->required(),
        ]);
    }

    /**
     * Populates the form with the entity's values.
     *
     * @param IEntity $entity
     *
     * @return void
     */
    protected function populateFormWithEntity(IEntity $entity)
    {
        /** @var PageEntity $entity */
        $this->title    = $entity->title;
        $this->subTitle = $entity->subTitle;
        $this->content  = $entity->content;
    }

    /**
     * Populates the form with the entity's values.
     *
     * @param IEntity $entity
     *
     * @return void
     */
    protected function populateEntityWithForm(IEntity $entity)
    {
        /** @var PageEntity $entity */
        $entity->title    = $this->title;
        $entity->subTitle = $this->subTitle;
        $entity->content  = $this->content;
    }
}