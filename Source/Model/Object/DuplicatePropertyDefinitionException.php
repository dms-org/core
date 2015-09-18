<?php

namespace Iddigital\Cms\Core\Model\Object;

use Iddigital\Cms\Core\Exception\BaseException;

/**
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DuplicatePropertyDefinitionException extends BaseException
{

    /**
     * DuplicatePropertyDefinitionException constructor.
     *
     * @param string     $class
     * @param int|string $name
     * @param            $file
     * @param            $line
     */
    public function __construct($class, $name, $file, $line)
    {
        parent::__construct(
                "Cannot build {$class}: duplicate property definition for \${$name} in file {$file} on line {$line}"
        );
    }
}