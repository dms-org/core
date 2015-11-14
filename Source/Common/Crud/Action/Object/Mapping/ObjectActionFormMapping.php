<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping;

use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectAction;
use Iddigital\Cms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IStagedForm;
use Iddigital\Cms\Core\Module\Mapping\StagedFormDtoMapping;
use Iddigital\Cms\Core\Util\Debug;

/**
 * The object action form mapping base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ObjectActionFormMapping extends StagedFormDtoMapping implements IObjectActionFormMapping
{
    /**
     * @var string|null
     */
    protected $dataDtoType;

    /**
     * @inheritDoc
     */
    public function __construct(IStagedForm $stagedForm, $dataDtoType = null)
    {
        $objectFormFirstStage = $stagedForm->getFirstStage()->loadForm();

        if (!$objectFormFirstStage->hasField(IObjectAction::OBJECT_FIELD_NAME)) {
            throw InvalidArgumentException::format(
                    'Invalid object form: must contain form field \'%s\', (%s) given',
                    IObjectAction::OBJECT_FIELD_NAME, Debug::formatValues($objectFormFirstStage->getFieldNames())
            );
        }

        parent::__construct($stagedForm, ObjectActionParameter::class);
        $this->dataDtoType = $dataDtoType;
    }

    /**
     * Gets the mapped data dto type or NULL if no type is mapped.
     *
     * @return string|null
     */
    public function getDataDtoType()
    {
        return $this->dataDtoType;
    }
}