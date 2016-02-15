<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Processor\BoolProcessor;
use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\Field\Processor\Validator\BoolValidator;
use Dms\Core\Form\Field\Processor\Validator\FloatValidator;
use Dms\Core\Form\Field\Processor\Validator\IntValidator;
use Dms\Core\Form\Field\Processor\Validator\RequiredValidator;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The scalar type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ScalarType extends FieldType
{
    const ATTR_TYPE = 'type';

    const STRING = IType::STRING;
    const INT = IType::INT;
    const FLOAT = IType::FLOAT;
    const BOOL = IType::BOOL;

    /**
     * ScalarType constructor.
     *
     * @param string $type
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $type)
    {
        if (!in_array($type, [self::STRING, self::INT, self::FLOAT, self::BOOL])) {
            throw InvalidArgumentException::format('Unknown scalar type: %s', $type);
        }

        $this->attributes[self::ATTR_TYPE] = $type;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->attributes[self::ATTR_TYPE];
    }

    /**
     * {@inheritDoc}
     */
    protected function buildPhpTypeOfInput() : IType
    {
        // Scalar inputs can be of mixed type
        // as they will be validated and coerced
        // into their expected php types by the
        // field processors
        return Type::mixed();
    }

    /**
     * @return IType
     */
    protected function getProcessedScalarType() : IType
    {
        $type = Type::scalar($this->attributes[self::ATTR_TYPE]);

        return $this->get(self::ATTR_REQUIRED) ? $type : $type->nullable();
    }

    /**
     * @inheritDoc
     */
    protected function hasTypeSpecificRequiredValidator() : bool
    {
        return $this->getType() === self::BOOL;
    }

    /**
     * @inheritDoc
     */
    protected function buildProcessors() : array
    {
        $processors = [];

        switch ($this->getType()) {
            case self::STRING:
                $processors[] = new TypeProcessor('string');
                break;

            case self::INT:
                $processors[] = new IntValidator($this->inputType);
                $processors[] = new TypeProcessor('int');
                break;

            case self::FLOAT:
                $processors[] = new FloatValidator($this->inputType);
                $processors[] = new TypeProcessor('float');
                break;

            case self::BOOL:
                $processors[] = new BoolValidator($this->inputType);
                $processors[] = new BoolProcessor();

                if ($this->get(self::ATTR_REQUIRED)) {
                    $processors[] = new RequiredValidator(Type::bool()->nullable());
                }
                break;
        }

        return $processors;
    }
}