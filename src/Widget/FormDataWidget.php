<?php declare(strict_types = 1);

namespace Dms\Core\Widget;

use Dms\Core\Auth\IAuthSystem;
use Dms\Core\Form\IForm;

/**
 * The form data widget class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FormDataWidget extends Widget
{
    /**
     * @var IForm
     */
    protected $form;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $label, IAuthSystem $authSystem, array $requiredPermissions, IForm $form)
    {
        parent::__construct($name, $label, $authSystem, $requiredPermissions);

        $this->form = $form;
    }

    /**
     * @return IForm
     */
    public function getForm() : IForm
    {
        return $this->form;
    }

    protected function hasExtraAuthorization() : bool
    {
        return true;
    }
}