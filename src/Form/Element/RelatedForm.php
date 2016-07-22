<?php

namespace SleepingOwl\Admin\Form\Element;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SleepingOwl\Admin\Contracts\Form\ElementsInterface;

class RelatedForm extends NamedFormElement implements ElementsInterface
{

    use \SleepingOwl\Admin\Traits\FormElements;

    /**
     * @var bool
     */
    protected $multiple = false;

    /**
     * RelatedForm constructor.
     *
     * @param string $path
     * @param null|string $label
     * @param array $elements
     */
    public function __construct($path, $label, array $elements = [])
    {
        $this->setElements($elements);
        parent::__construct($path, $label);
    }

    public function initialize()
    {
        parent::initialize();
        $this->initializeElements();
    }

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        parent::setModel($model);

        $parts    = explode('.', $this->getPath());
        $relation = null;
        $path     = ['related'];

        while (count($parts) > 0) {
            $part = array_shift($parts);
            if (method_exists($model, $part)) {
                $relation = $model->{$part}();

                if (($relation instanceof HasOne) or ($relation instanceof BelongsTo)) {
                    $model  = $relation->getModel();
                    $path[] = $part;
                    continue;
                } else if (($relation instanceof HasManyThrough) or ($relation instanceof HasMany) or ($relation instanceof BelongsToMany)) {
                    $model          = $relation->getModel();
                    $this->multiple = true;
                    $path[]         = $part;
                    break;
                }
            }
        }

        $this->setPath(implode('.', $path));
        $this->setModelForElements($model);


        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $this->setPathForElements($this->getPath());

        return parent::toArray() + [
            'items' => $this->getElements(),
        ];
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    protected function setPathForElements($path)
    {
        $this->getElements()->each(function ($element) use ($path) {
            $element = $this->getElementContainer($element);
            if ($element instanceof NamedFormElement) {
                $element->setPath("{$path}.{$element->getPath()}");
            }
        });

        return $this;
    }
}
