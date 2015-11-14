<?php

namespace Iddigital\Cms\Core\Common\Crud\Action\Object;

use Iddigital\Cms\Core\Auth\IAuthSystem;
use Iddigital\Cms\Core\Exception\TypeMismatchException;
use Iddigital\Cms\Core\Module\Action\ParameterizedAction;

/**
 * The object action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectAction extends ParameterizedAction implements IObjectAction
{
    /**
     * @inheritDoc
     */
    public function __construct(
            $name,
            IAuthSystem $auth,
            array $requiredPermissions,
            IObjectActionFormMapping $formDtoMapping,
            IObjectActionHandler $handler
    ) {
        if ($formDtoMapping->getDataDtoType() !== $handler->getDataDtoType()) {
            throw TypeMismatchException::format(
                    'Cannot construct %s: data dto type %s does not match handler data dto type %s',
                    __METHOD__, $formDtoMapping->getDtoType() ?: 'null', $handler->getDtoType() ?: 'null'
            );
        }

        parent::__construct($name, $auth, $requiredPermissions, $formDtoMapping, $handler);
    }

    /**
     * @inheritDoc
     */
    public function runOnObject($object, array $data)
    {
        return $this
                ->withSubmittedFirstStage([self::OBJECT_FIELD_NAME => $object])
                ->run($data);
    }
}