<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Object\Mapping;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Object\IObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\ObjectActionParameter;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IForm;
use Dms\Core\Form\IStagedForm;
use Dms\Core\Module\Mapping\StagedFormDtoMapping;
use Dms\Core\Util\Debug;

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

        if ($objectFormFirstStage->getFieldNames() !== [IObjectAction::OBJECT_FIELD_NAME]) {
            throw InvalidArgumentException::format(
                    'Invalid object form: must contain only the \'%s\' form field , (%s) given',
                    IObjectAction::OBJECT_FIELD_NAME, Debug::formatValues($objectFormFirstStage->getFieldNames())
            );
        }

        parent::__construct($stagedForm, ObjectActionParameter::class);
        $this->dataDtoType = $dataDtoType;
    }

    /**
     * @inheritDoc
     */
    public function getObjectForm() : \Dms\Core\Form\IForm
    {
        return $this->stagedForm->getFirstForm();
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