<?php declare(strict_types = 1);

namespace Dms\Core\Table;

use Dms\Core\Form\IField;

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
     * @see ConditionOperator
     *
     * @return string
     */
    public function getOperator() : string;

    /**
     * Gets the equivalent form field for this condition operator.
     *
     * @return IField
     */
    public function getField() : \Dms\Core\Form\IField;

    /**
     * Returns an equivalent operator with the field with
     * the new name and label.
     *
     * @param string $name
     * @param string $label
     *
     * @return static
     */
    public function withFieldAs(string $name, string $label);
}