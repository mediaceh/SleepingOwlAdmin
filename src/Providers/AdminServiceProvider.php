<?php

namespace SleepingOwl\Admin\Providers;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use SleepingOwl\Admin\Admin;
use SleepingOwl\Admin\AliasBinder;
use SleepingOwl\Admin\Contracts\Display\TableHeaderColumnInterface;
use SleepingOwl\Admin\Contracts\FormButtonsInterface;
use SleepingOwl\Admin\Contracts\RepositoryInterface;
use SleepingOwl\Admin\Contracts\Widgets\WidgetsRegistryInterface;
use SleepingOwl\Admin\Model\ModelConfiguration;
use SleepingOwl\Admin\Model\ModelConfigurationManager;
use SleepingOwl\Admin\Widgets\WidgetsRegistry;
use Symfony\Component\Finder\Finder;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $directory;

    public function register()
    {
        $this->app->singleton('sleeping_owl', function () {
            return new Admin();
        });

        $this->app->alias('sleeping_owl', \SleepingOwl\Admin\Admin::class);

        $this->initializeNavigation();
        $this->registerWysiwyg();
        $this->registerAliases();

        $this->app->singleton(WidgetsRegistryInterface::class, function () {
            return new WidgetsRegistry($this->app);
        });

        $this->app->booted(function () {
            $this->app[WidgetsRegistryInterface::class]->placeWidgets(
                $this->app[ViewFactory::class]
            );
        });

        $this->app->booted(function () {
            $this->registerCustomRoutes();
            $this->registerDefaultRoutes();
            $this->registerNavigationFile();
        });

        ModelConfigurationManager::setEventDispatcher($this->app['events']);
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getConfig($key)
    {
        return $this->app['config']->get('sleeping_owl.'.$key);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getBootstrapPath($path = null)
    {
        if (! is_null($path)) {
            $path = DIRECTORY_SEPARATOR.$path;
        }

        return $this->getConfig('bootstrapDirectory').$path;
    }

    public function boot()
    {
        $this->app[WidgetsRegistryInterface::class]->registerWidget(\SleepingOwl\Admin\Widgets\SuccessMessages::class);

        $this->app->singleton('sleeping_owl.template', function () {
            return $this->app['sleeping_owl']->template();
        });

        $this->registerBootstrap();

        $this->registerRoutes(function (Router $route) {
            $route->group(['as' => 'admin.', 'namespace' => 'SleepingOwl\Admin\Http\Controllers'], function ($route) {
                $route->get('assets/admin.scripts', [
                    'as'   => 'scripts',
                    'uses' => 'AdminController@getScripts',
                ]);
            });
        });
    }

    protected function initializeNavigation()
    {
        $this->app->bind(TableHeaderColumnInterface::class, \SleepingOwl\Admin\Display\TableHeaderColumn::class);
        $this->app->bind(RepositoryInterface::class, \SleepingOwl\Admin\Repository\BaseRepository::class);
        $this->app->bind(FormButtonsInterface::class, \SleepingOwl\Admin\Form\FormButtons::class);

        $this->app->bind(\KodiComponents\Navigation\Contracts\PageInterface::class, \SleepingOwl\Admin\Navigation\Page::class);
        $this->app->bind(\KodiComponents\Navigation\Contracts\BadgeInterface::class, \SleepingOwl\Admin\Navigation\Badge::class);

        $this->app->singleton('sleeping_owl.navigation', function () {
            return new \SleepingOwl\Admin\Navigation();
        });
    }

    protected function registerWysiwyg()
    {
        $this->app->singleton('sleeping_owl.wysiwyg', function () {
            return new \SleepingOwl\Admin\Wysiwyg\Manager();
        });
    }

    /**
     * @return array
     */
    protected function registerBootstrap()
    {
        $directory = $this->getBootstrapPath();

        if (! is_dir($directory)) {
            return;
        }

        $files = Finder::create()
            ->files()
            ->name('/^.+\.php$/')
            ->notName('routes.php')
            ->notName('navigation.php')
            ->in($directory)
            ->sort(function ($a) {
                return $a->getFilename() != 'bootstrap.php';
            });

        foreach ($files as $file) {
            require $file;
        }
    }

    protected function registerAliases()
    {
        AliasLoader::getInstance(config('sleeping_owl.aliases', []));
    }

    protected function registerCustomRoutes()
    {
        if (file_exists($file = $this->getBootstrapPath('routes.php'))) {
            $this->registerRoutes(function (Router $route) use ($file) {
                require $file;
            });
        }
    }

    protected function registerDefaultRoutes()
    {
        $this->registerRoutes(function (Router $router) {
            $router->pattern('adminModelId', '[a-zA-Z0-9_-]+');

            $aliases = $this->app['sleeping_owl']->modelAliases();

            if (count($aliases) > 0) {
                $router->pattern('adminModel', implode('|', $aliases));

                $this->app['router']->bind('adminModel', function ($model, \Illuminate\Routing\Route $route) use ($aliases) {
                    $class = array_search($model, $aliases);

                    if ($class === false) {
                        throw new ModelNotFoundException;
                    }

                    /** @var ModelConfiguration $model */
                    $model = $this->app['sleeping_owl']->getModel($class);

                    if ($model->hasCustomControllerClass() && $route->getActionName() !== 'Closure') {
                        list($controller, $action) = explode('@', $route->getActionName(), 2);

                        $newController = $model->getControllerClass().'@'.$action;

                        $route->uses($newController);
                    }

                    return $model;
                });
            }

            if (file_exists($routesFile = __DIR__.'/../Http/routes.php')) {
                require $routesFile;
            }

            foreach (AliasBinder::routes() as $route) {
                call_user_func($route, $router);
            }
        });
    }

    /**
     * @param \Closure $callback
     */
    protected function registerRoutes(\Closure $callback)
    {
        $this->app['router']->group([
            'prefix' => $this->getConfig('url_prefix'),
            'middleware' => $this->getConfig('middleware'),
        ], function ($route) use ($callback) {
            call_user_func($callback, $route);
        });
    }

    protected function registerNavigationFile()
    {
        if (file_exists($navigation = $this->getBootstrapPath('navigation.php'))) {
            $items = include $navigation;

            if (is_array($items)) {
                $this->app['sleeping_owl.navigation']->setFromArray($items);
            }
        }
    }
}
