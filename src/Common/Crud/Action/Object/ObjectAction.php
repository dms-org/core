<?php declare(strict_types = 1);

namespace Dms\Core\Common\Crud\Action\Object;

use Dms\Core\Auth\IAuthSystemInPackageContext;
use Dms\Core\Exception\TypeMismatchException;
use Dms\Core\Form\Field\Processor\FieldValidator;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Module\Action\ParameterizedAction;

/**
 * The object action class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectAction extends ParameterizedAction implements IObjectAction
{
    /**
     * @var IObjectActionHandler
     */
    protected $handler;

    /**
     * @var IObjectActionFormMapping
     */
    protected $formDtoMapping;

    /**
     * @var FieldValidator[]
     */
    protected $objectValidators;

    /**
     * @inheritDoc
     */
    public function __construct(
        $name,
        IAuthSystemInPackageContext $auth,
        array $requiredPermissions,
        IObjectActionFormMapping $formDtoMapping,
        IObjectActionHandler $handler,
        array $metadata = []
    ) {
        if ($formDtoMapping->getDataDtoType() !== $handler->getDataDtoType()) {
            throw TypeMismatchException::format(
                'Cannot construct %s: data dto type %s does not match handler data dto type %s',
                __METHOD__, $formDtoMapping->getDtoType() ?: 'null', $handler->getParameterTypeClass() ?: 'null'
            );
        }

        parent::__construct($name, $auth, $requiredPermissions, $formDtoMapping, $handler, $metadata);
    }

    /**
     * @inheritDoc
     */
    public function getObjectForm() : \Dms\Core\Form\IForm
    {
        return $this->formDtoMapping->getObjectForm();
    }

    /**
     * @inheritDoc
     */
    public function getObjectType() : string
    {
        return $this->handler->getObjectType();
    }

    /**
     * @return FieldValidator[]
     */
    protected function getObjectValidators() : array
    {
        if ($this->objectValidators === null) {
            $objectType = Type::object($this->handler->getObjectType());

            $processors = $this->formDtoMapping
                ->getObjectForm()
                ->getField(IObjectAction::OBJECT_FIELD_NAME)
                ->getProcessors();

            /** @var FieldValidator[] $objectValidators */
            $this->objectValidators = [];

            foreach ($processors as $processor) {
                if ($processor instanceof FieldValidator && $processor->getInputType()->isSupersetOf($objectType)) {
                    $this->objectValidators[] = $processor;
                }
            }
        }

        return $this->objectValidators;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedObjects(array $objects) : array
    {
        TypeMismatchException::verifyAllInstanceOf(__METHOD__, 'objects', $objects, $this->handler->getObjectType());

        /** @var FieldValidator[] $objectValidators */
        $objectValidators = $this->getObjectValidators();

        $validObjects = [];
        foreach ($objects as $key => $object) {
            $messages = [];

            foreach ($objectValidators as $validator) {
                $validator->process($object, $messages);
            }

            if (empty($messages)) {
                $validObjects[$key] = $object;
            }
        }


        return $validObjects;
    }

    /**
     * @inheritDoc
     */
    public function isSupported($object) : bool
    {
        /** @var FieldValidator[] $objectValidators */
        $objectValidators = $this->getObjectValidators();

        $messages = [];

        foreach ($objectValidators as $validator) {
            $validator->process($object, $messages);

            if (!empty($messages)) {
                return false;
            }
        }

        return true;
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