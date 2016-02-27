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
     * Gets the section's title or '' if the section continues from the previous section.
     *
     * @return string
     */
    public function getTitle() : string;

    /**
     * Returns whether the current form section is a continuation of the
     * previous section within the parent form / staged form.
     *
     * @return bool
     */
    public function doesContinuePreviousSection() : bool;

    /**
     * Gets the sections fields.
     *
     * @return IField[]
     */
    public function getFields() : array;
}
