<?php

namespace Dms\Core\Form\Field\Type;

use Dms\Core\Form\Field\Processor\TypeProcessor;
use Dms\Core\Form\IFieldProcessor;

/**
 * The string type class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class StringType extends ScalarType
{
    const ATTR_TYPE = 'type';
    const ATTR_MIN_LENGTH = 'min-length';
    const ATTR_MAX_LENGTH = 'max-length';

    const TYPE_PASSWORD = 'password';
    const TYPE_EMAIL = 'email';
    const TYPE_URL = 'url';
    const TYPE_HTML = 'html';

    public function __construct()
    {
        parent::__construct(self::STRING);
    }

    /**
     * @return IFieldProcessor[]
     */
    protected function buildProcessors()
    {
        return [
                new TypeProcessor('string'),
        ];
    }
}