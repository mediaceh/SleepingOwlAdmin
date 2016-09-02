<?php

namespace SleepingOwl\Admin\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ColumnFilterInterface extends Initializable
{
    /**
     * @param NamedColumnInterface $column
     * @param Builder              $query
     * @param string               $search
     * @param array|string         $fullSearch
     *
     * @return void
     */
    public function apply(
        NamedColumnInterface $column,
        Builder $query,
        $search,
        $fullSearch
    );

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
