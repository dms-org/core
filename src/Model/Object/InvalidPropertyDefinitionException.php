<?php declare(strict_types = 1);

namespace Dms\Core\Model\Object;

use Dms\Core\Exception;

/**
 * Exception for property reference which could not be matched
 * with the defined class properties.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InvalidPropertyDefinitionException extends Exception\BaseException
{
    public function __construct($class, $file, $line)
    {
        parent::__construct(
                "Cannot build {$class}: could not find instance property from call to ClassDefinition::property() in file {$file} on line {$line}"
        );
    }

}