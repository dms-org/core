<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\Validator\MaxDecimalPointsValidator;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The float type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FloatType extends NumericType
{
    const ATTR_MAX_DECIMAL_POINTS = 'max-decimal-points';

    public function __construct()
    {
        parent::__construct(self::FLOAT);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors() : array
    {
        $processors = parent::buildProcessors();

        if ($this->has(self::ATTR_MAX_DECIMAL_POINTS)) {
            $processors[] = new MaxDecimalPointsValidator(
                    Type::float(),
                    $this->get(self::ATTR_MAX_DECIMAL_POINTS)
            );
        }

        return $processors;
    }
}