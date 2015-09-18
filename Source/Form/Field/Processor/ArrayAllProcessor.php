<?php

namespace Iddigital\Cms\Core\Form\Field\Processor;

use Iddigital\Cms\Core\Exception\InvalidArgumentException;
use Iddigital\Cms\Core\Form\IFieldProcessor;
use Iddigital\Cms\Core\Language\Message;
use Iddigital\Cms\Core\Model\Type\ArrayType;

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
     */
    public function __construct(array $elementProcessors)
    {
        InvalidArgumentException::verify(!empty($elementProcessors), 'element processors cannot be empty');
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'elementProcessors', $elementProcessors, IFieldProcessor::class);
        /** @var IFieldProcessor $lastProcessor */
        $lastProcessor = end($elementProcessors);
        parent::__construct(new ArrayType($lastProcessor->getProcessedType()));

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