<?php

namespace Iddigital\Cms\Core\Form;

/**
 * The form section interface.
 * 
 * @author Elliot Levin <elliot@aanet.com.au>
 */
interface IFormSection
{
    /**
     * Gets the section's title.
     * 
     * @return string
     */
    public function getTitle();

    /**
     * Gets the sections fields.
     *
     * @return IField[]
     */
    public function getFields();
}
