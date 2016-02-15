<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

/**
 * The integer type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class IntType extends NumericType
{
    public function __construct()
    {
        parent::__construct(self::INT);
    }
}