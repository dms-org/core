<?php declare(strict_types = 1);

namespace Dms\Core\Module\Definition\Widget;

/**
 * The widget definer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WidgetLabelDefiner extends WidgetDefinerBase
{
    /**
     * Defines the label of the widget
     *
     * @param string $label
     *
     * @return WidgetTypeDefiner
     */
    public function label(string $label) : WidgetTypeDefiner
    {
        return new WidgetTypeDefiner($this->name, $label, $this->authSystem, $this->requiredPermissions, $this->tables, $this->charts, $this->actions, $this->callback);
    }
}