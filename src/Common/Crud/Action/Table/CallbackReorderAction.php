<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Table;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Auth\IPermission;
use Dms\Core\Common\Crud\Action\Object\CustomObjectActionHandler;
use Dms\Core\Common\Crud\Action\Object\Mapping\ArrayObjectActionFormMapping;
use Dms\Core\Common\Crud\Action\Object\ObjectAction;
use Dms\Core\Common\Crud\Form\ObjectForm;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Builder\Form;
use Dms\Core\Form\Builder\StagedForm;
use Dms\Core\Form\Field\Builder\Field;
use Dms\Core\Model\IEntitySet;
use Dms\Core\Model\Object\ArrayDataObject;

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
            string $name,
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
                        Field::name(self::NEW_INDEX_FIELD_NAME)->label('New Index')->int()->min(1)->required()
                ]))
               ->build();
    }

    /**
     * @inheritdoc
     */
    public function runReorder($object, int $newIndex)
    {
        $this->runOnObject($object, [self::NEW_INDEX_FIELD_NAME => $newIndex]);
    }
}