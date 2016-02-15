<?php declare(strict_types = 1);

namespace Dms\Core\Form;

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
    public function getTitle() : string;

    /**
     * Gets the sections fields.
     *
     * @return IField[]
     */
    public function getFields() : array;
}
