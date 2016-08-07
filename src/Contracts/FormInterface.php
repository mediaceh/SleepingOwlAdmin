<?php

namespace SleepingOwl\Admin\Contracts;

use SleepingOwl\Admin\Contracts\Form\ElementsInterface;

interface FormInterface extends FormElementInterface, ElementsInterface
{
    /**
     * Set form action url.
     *
     * @param string $action
     */
    public function setAction($action);

    /**
     * Set form model instance id.
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * @return \Illuminate\Contracts\Validation\Validator|null
     */
    public function validateForm();

    /**
     * Save model.
     */
    public function saveForm();
}
