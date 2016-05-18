<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\Field\Processor\InnerCrudModuleProcessor;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Object\TypedObject;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType as IPhpType;

/**
 * The inner crud module type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerCrudModuleType extends FieldType
{
    /**
     * @var IReadModule
     */
    private $module;

    /**
     * InnerCrudModuleType constructor.
     *
     * @param IReadModule $readModule
     */
    public function __construct(IReadModule $readModule)
    {
        $this->module = $readModule;

        if (!($readModule instanceof ICrudModule)) {
            $this->attributes[self::ATTR_READ_ONLY] = true;
        }

        /** @var TypedObject|string $objectType */
        $objectType                           = $readModule->getObjectType();
        $this->attributes[self::ATTR_DEFAULT] = $objectType::collection();

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function initializeFromCurrentAttributes()
    {
        if ($this->has(self::ATTR_INITIAL_VALUE)) {
            $this->module = $this->module->withDataSource($this->get(self::ATTR_INITIAL_VALUE));
        }
        
        if ($this->get(self::ATTR_READ_ONLY) && !$this->has(self::ATTR_INITIAL_VALUE)) {
            $this->attributes[self::ATTR_INITIAL_VALUE] = $this->module->getDataSource()->subset($this->module->getDataSource()->criteria());
        }

        parent::initializeFromCurrentAttributes();
    }


    /**
     * @return IReadModule
     */
    public function getModule() : IReadModule
    {
        return $this->module;
    }

    /**
     * @return bool
     */
    public function isCrudModule() : bool
    {
        return $this->module instanceof ICrudModule;
    }

    /**
     * @return IPhpType
     */
    protected function buildPhpTypeOfInput() : IPhpType
    {
        return Type::arrayOf(Type::arrayOf(Type::mixed()));
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        return $this->module instanceof ICrudModule
                ? [new InnerCrudModuleProcessor($this->module)]
                : [];
    }
}