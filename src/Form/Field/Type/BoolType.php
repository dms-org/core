<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Type;

/**
 * The bool type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class BoolType extends ScalarType
{
    public function __construct()
    {
        parent::__construct(self::BOOL);
    }
}