<?php declare(strict_types = 1);

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Form\Field\Processor\Validator\TypeValidator;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\Builder\Type;

/**
 * The field type processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TypeProcessor extends FieldProcessor
{
    const MESSAGE = TypeValidator::MESSAGE;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $type = strtolower($type);

        parent::__construct(Type::scalar($type));

        $this->type = $type;
    }

    protected function doProcess($input, array &$messages)
    {
        if (is_array($input)) {
            $messages[] = new Message(self::MESSAGE, ['type' => $this->type]);
        }

        if (is_object($input) && !method_exists($input, '__toString')) {
            $messages[] = new Message(self::MESSAGE, ['type' => $this->type]);
        }

        if (empty($messages)) {
            settype($input, $this->type);

            return $input;
        } else {
            return null;
        }
    }

    protected function doUnprocess($input)
    {
        return $input;
    }
}