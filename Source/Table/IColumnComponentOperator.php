<?php

namespace Iddigital\Cms\Core\Table;

use Iddigital\Cms\Core\Form\IField;

/**
 * The column condition operator interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IColumnComponentOperator
{
    /**
     * Gets the condition operator.
     *
     * @return string
     */
    public function getOperator();

    /**
     * Gets the equivalent form field for this condition operator.
     *
     * @return IField
     */
    public function getField();
}