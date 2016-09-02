<?php

namespace SleepingOwl\Admin\Form\Element;

use SleepingOwl\Admin\Contracts\Template\MetaInterface;
use SleepingOwl\Admin\Contracts\Template\TemplateInterface;
use SleepingOwl\Admin\Contracts\Wysiwyg\WysiwygMangerInterface;

class CKEditor extends Wysiwyg
{

    /**
     * @var string
     */
    protected $view = 'form.element.wysiwyg';

    /**
     * @param MetaInterface $meta
     * @param WysiwygMangerInterface $manger
     * @param string $path
     * @param string|null $label
     */
    public function __construct(MetaInterface $meta, WysiwygMangerInterface $manger, $path, $label = null)
    {
        parent::__construct($meta, $manger, $path, $label, 'ckeditor');
    }
}
