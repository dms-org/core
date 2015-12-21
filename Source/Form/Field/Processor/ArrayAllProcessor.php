<?php

namespace Dms\Core\Form\Field\Processor;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IFieldProcessor;
use Dms\Core\Language\Message;
use Dms\Core\Model\Type\ArrayType;
use Dms\Core\Model\Type\Builder\Type;
use Dms\Core\Model\Type\IType;

/**
 * The array processor.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ArrayAllProcessor extends FieldProcessor
{
    /**
     * @var IFieldProcessor[]
     */
    protected $elementProcessors;

    /**
     * @param IFieldProcessor[] $elementProcessors
     * @param IType|null             $elementType
     */
    public function __construct(array $elementProcessors, IType $elementType = null)
    {
        InvalidArgumentException::verify(!empty($elementProcessors), 'element processors cannot be empty');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'elementProcessors', $elementProcessors, IFieldProcessor::class);

        /** @var IFieldProcessor $lastProcessor */
        $lastProcessor = end($elementProcessors);

        parent::__construct(Type::arrayOf($elementType ?: $lastProcessor->getProcessedType()));

        $this->elementProcessors = $elementProcessors;
    }

    protected function doProcess($input, array &$messages)
    {
        foreach ($input as $key => $element) {
            foreach ($this->elementProcessors as $processor) {
                /** @var Message[] $newMessages */
                $newMessages = [];
                $element = $processor->process($element, $newMessages);

                if (!empty($newMessages)) {
                    foreach ($newMessages as $newMessage) {
                        $messages[] = $newMessage->withParameters([
                                'key' => $key
                        ]);
                    }

                    continue 2;
                }
            }

            $input[$key] = $element;
        }

        return $input;
    }

    protected function doUnprocess($input)
    {
        foreach ($input as $key => $element) {
            foreach ($this->elementProcessors as $processor) {
                $input[$key] = $processor->unprocess($element);
            }
        }

        return $input;
    }
}