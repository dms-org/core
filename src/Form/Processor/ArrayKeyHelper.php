<?php

namespace Dms\Core\Form\Processor;

/**
 * The array key helper.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayKeyHelper
{
    public static function mapArrayKeys(array $input, array $keyMap)
    {
        $mappedInput = [];

        foreach ($input as $key => $value) {
            $newKey               = isset($keyMap[$key]) ? $keyMap[$key] : $key;
            $mappedInput[$newKey] = $value;
        }

        return $mappedInput;
    }
}