<?php

namespace SleepingOwl\Admin\Form\Element;

use Illuminate\Database\Eloquent\Model;
use Request;
use SleepingOwl\Admin\Model\Upload;

class Images extends Image
{
    public function save()
    {
        $name = $this->getName();
        $value = Request::input($name, '');

        if (! empty($value)) {
            $value = explode(',', $value);
        } else {
            $value = [];
        }

        Request::merge([$name => $value]);
        parent::save();
    }

    /**
     * @param Model  $model
     * @param string $attribute
     * @param array  $values
     */
    protected function setValue(Model $model, $attribute, $values)
    {
        foreach ($values as $i => $file) {
            $file = Upload::whereFile($file)->first();

            if (! is_null($file)) {
                $values[$i] = $this->saveFile($file);
            }
        }

        $model->setAttribute($attribute, $values);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();
        if (is_null($value)) {
            $value = [];
        }

        if (is_string($value)) {
            $value = preg_split('/,/', $value, -1, PREG_SPLIT_NO_EMPTY);
        }

        return $value;
    }
}
