<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Table;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectAction;
use Iddigital\Cms\Core\Common\Crud\Form\ObjectForm;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Builder\StagedForm;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\IEntitySet;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;

/**
 * The reorder index action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CallbackReorderAction extends ObjectAction implements IReorderAction
{
    /**
     * @param IEntitySet    $dataSource
     * @param string        $name
     * @param IAuthSystem   $auth
     * @param IPermission[] $requiredPermissions
     * @param callable      $reorderCallback
     *
     * @throws TypeMismatchException
     */
    public function __construct(
            IEntitySet $dataSource,
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            callable $reorderCallback
    ) {
        parent::__construct(
                $name,
                $auth,
                $requiredPermissions,
                new ArrayObjectActionFormMapping($this->reorderForm($dataSource)),
                new CustomObjectActionHandler(function ($object, ArrayDataObject $data) use ($reorderCallback) {
                    $reorderCallback($object, $data[self::NEW_INDEX_FIELD_NAME]);
                }, null, $dataSource->getEntityType(), ArrayDataObject::class)
        );
    }

    protected function reorderForm(IEntitySet $dataSource)
    {
        return StagedForm::begin(ObjectForm::build($dataSource))
                ->then(Form::create()->section('Reorder', [
                        Field::name(self::NEW_INDEX_FIELD_NAME)->label('New Index')->int()->min(1)
                ]))
               ->build();
    }

    /**
     * @inheritdoc
     */
    public function runReorder($object, $newIndex)
    {
        $this->runOnObject($object, [self::NEW_INDEX_FIELD_NAME => $newIndex]);
    }
}