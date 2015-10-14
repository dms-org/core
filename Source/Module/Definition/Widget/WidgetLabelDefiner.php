<?php

namespace Iddigital\Cms\Core\Module\Definition\Widget;

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
    public function label($label)
    {
        return new WidgetTypeDefiner($this->name, $label, $this->tables, $this->charts, $this->callback);
    }
}