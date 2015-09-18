<?php

namespace Iddigital\Cms\Core\Util;

interface IClock
{
    /**
     * Gets the current time.
     * 
     * @return \DateTime
     */
    public function now();
}
