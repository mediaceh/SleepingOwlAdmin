<?php

namespace SleepingOwl\Admin\Contracts;

interface NamedColumnInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * @param ModelConfigurationInterface $model
     *
     * @return $this
     */
    public function setModelConfiguration(ModelConfigurationInterface $model);

    /**
     * @return ModelConfigurationInterface
     */
    public function getModelConfiguration();
}
