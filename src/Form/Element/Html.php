<?php

namespace SleepingOwl\Admin\Form\Element;

use Closure;
use SleepingOwl\Admin\Contracts\Template\MetaInterface;
use SleepingOwl\Admin\Contracts\Template\TemplateInterface;

class Html extends Custom
{

    /**
     * Custom constructor.
     *
     * @param MetaInterface $meta
     * @param string|Closure $html
     */
    public function __construct(MetaInterface $meta, $html)
    {
        $this->setDisplay($html);

        parent::__construct($meta);
    }
}
