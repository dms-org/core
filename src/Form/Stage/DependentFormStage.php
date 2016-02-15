<?php declare(strict_types = 1);

namespace Dms\Core\Form\Stage;

use Dms\Core\Exception\InvalidArgumentException;

/**
 * The dependent form stage base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DependentFormStage extends FormStage
{
    /**
     * @var callable
     */
    protected $loadFormCallback;

    /**
     * @var string[]
     */
    protected $definedFieldNames;

    /**
     * @var string[]
     */
    protected $requiredFieldNames;

    /**
     * DependentFormStage constructor.
     *
     * @param callable      $loadFormCallback
     * @param string[]      $definedFieldNames
     * @param string[]|null $requiredFieldNames NULL if all fields are required.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(callable $loadFormCallback, array $definedFieldNames, array $requiredFieldNames = null)
    {
        InvalidArgumentException::verify(
                $requiredFieldNames === null || !empty($requiredFieldNames),
                'Required field names cannot be empty'
        );

        if ($requiredFieldNames !== null) {
            InvalidArgumentException::verifyAll(__METHOD__, 'requiredFieldNames', $requiredFieldNames, 'is_string');
        }

        $this->loadFormCallback   = $loadFormCallback;
        $this->definedFieldNames  = $definedFieldNames;
        $this->requiredFieldNames = $requiredFieldNames;
    }

    /**
     * @inheritDoc
     */
    public function getRequiredFieldNames()
    {
        return $this->requiredFieldNames;
    }

    /**
     * @inheritDoc
     */
    public function getDefinedFieldNames() : array
    {
        return $this->definedFieldNames;
    }

    /**
     * @inheritDoc
     */
    protected function getForm(array $previousSubmission = null)
    {
        return call_user_func($this->loadFormCallback, $previousSubmission);
    }
}