<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Table;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Auth\IPermission;
use Iddigital\Cms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Iddigital\Cms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Iddigital\Cms\Core\Common\Crud\Action\Object\ObjectAction;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Form\Builder\Form;
use Iddigital\Cms\Core\Form\Field\Builder\Field;
use Iddigital\Cms\Core\Model\Object\ArrayDataObject;

/**
 * The reorder index action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CallbackReorderAction extends ObjectAction implements IReorderAction
{
    /**
     * @param string        $name
     * @param IAuthSystem   $auth
     * @param IPermission[] $requiredPermissions
     * @param callable      $reorderCallback
     *
     * @throws TypeMismatchException
     */
    public function __construct(
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            callable $reorderCallback
    ) {
        parent::__construct(
                $name,
                $auth,
                $requiredPermissions,
                new ArrayObjectActionFormMapping($this->reorderForm()),
                new CustomObjectActionHandler(function ($object, ArrayDataObject $data) use ($reorderCallback) {
                    $reorderCallback($object, $data[self::INDEX_FIELD_NAME]);
                })
        );
    }

    protected function reorderForm()
    {
        return Form::create()->section('Reorder', [
                Field::name(self::INDEX_FIELD_NAME)->label('New Index')->int()->min(0)
        ])->build()->asStagedForm();
    }

    /**
     * @inheritdoc
     */
    public function runReorder($object, $newIndex)
    {
        $this->runOnObject($object, [self::INDEX_FIELD_NAME => $newIndex]);
    }
}