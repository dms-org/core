<?php declare(strict_types = 1);

namespace Dms\Core\Form\Processor;

use Dms\Core\Language\Message;

/**
 * The custom form processor class.
 *
 * @author Elliot Levin <elliot@aanet.com.au>
 */
class CustomFormProcessor extends FormProcessor
{
    /**
     * @var callable
     */
    protected $processCallback;

    /**
     * @var callable
     */
    protected $unprocessCallback;

    public function __construct(callable $processCallback, callable $unprocessCallback)
    {
        $this->processCallback   = $processCallback;
        $this->unprocessCallback = $unprocessCallback;
    }

    /**
     * @param array     $input
     * @param Message[] $messages
     *
     * @return array
     */
    protected function doProcess(array $input, array &$messages) : array
    {
        return call_user_func_array($this->processCallback, [$input, &$messages]);
    }

    /**
     * @param array $input
     *
     * @return array
     */
    protected function doUnprocess(array $input) : array
    {
        return call_user_func($this->unprocessCallback, $input);
    }

    /**
     * @inheritdoc
     */
    public function withFieldNames(array $fieldNameMap)
    {
        $inverseFieldMap = array_flip($fieldNameMap);

        return new self(
                function (array $input, array &$messages) use ($fieldNameMap, $inverseFieldMap) {
                    return ArrayKeyHelper::mapArrayKeys(
                            $this->process(
                                    ArrayKeyHelper::mapArrayKeys($input, $inverseFieldMap),
                                    $messages
                            ),
                            $fieldNameMap
                    );
                },
                function (array $input) use ($fieldNameMap, $inverseFieldMap) {
                    return ArrayKeyHelper::mapArrayKeys(
                            $this->unprocess(ArrayKeyHelper::mapArrayKeys($input, $inverseFieldMap)),
                            $fieldNameMap
                    );
                }
        );
    }
}
