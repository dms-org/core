<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\Validator\DecimalPointsValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The float type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FloatType extends NumericType
{
    const ATTR_MIN_DECIMAL_POINTS = 'min-decimal-points';
    const ATTR_MAX_DECIMAL_POINTS = 'max-decimal-points';
    const ATTR_EXACT_DECIMAL_POINTS = 'exact-decimal-points';

    public function __construct()
    {
        parent::__construct(self::FLOAT);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        $processors = parent::buildProcessors();

        if ($this->has(self::ATTR_MIN_DECIMAL_POINTS) || $this->has(self::ATTR_MAX_DECIMAL_POINTS)) {
            $processors[] = new DecimalPointsValidator(
                    Type::float(),
                    $this->get(self::ATTR_MIN_DECIMAL_POINTS),
                    $this->get(self::ATTR_MAX_DECIMAL_POINTS)
            );
        }

        if ($this->has(self::ATTR_EXACT_DECIMAL_POINTS)) {
            $processors[] = new DecimalPointsValidator(
                    Type::float(),
                    $this->get(self::ATTR_EXACT_DECIMAL_POINTS),
                    $this->get(self::ATTR_EXACT_DECIMAL_POINTS)
            );
        }

        return $processors;
    }
}