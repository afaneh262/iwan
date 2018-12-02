<?php

namespace Afaneh262\Iwan;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Intervention\Image\ImageServiceProvider;
use Larapack\DoctrineSupport\DoctrineSupportServiceProvider;
//use Larapack\VoyagerHooks\VoyagerHooksServiceProvider;
use Afaneh262\Iwan\Events\FormFieldsRegistered;
use Afaneh262\Iwan\Facades\Iwan as IwanFacade;
use Afaneh262\Iwan\FormFields\After\DescriptionHandler;
use Afaneh262\Iwan\Http\Middleware\IwanAdminMiddleware;
use Afaneh262\Iwan\Models\MenuItem;
use Afaneh262\Iwan\Models\Setting;
use Afaneh262\Iwan\Policies\BasePolicy;
use Afaneh262\Iwan\Policies\MenuItemPolicy;
use Afaneh262\Iwan\Policies\SettingPolicy;
use Afaneh262\Iwan\Providers\IwanDummyServiceProvider;
use Afaneh262\Iwan\Providers\IwanEventServiceProvider;
use Afaneh262\Iwan\Translator\Collection as TranslatorCollection;

class IwanServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Setting::class  => SettingPolicy::class,
        MenuItem::class => MenuItemPolicy::class,
    ];

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->register(IwanEventServiceProvider::class);
        $this->app->register(ImageServiceProvider::class);
        $this->app->register(IwanDummyServiceProvider::class);
        //$this->app->register(VoyagerHooksServiceProvider::class);
        $this->app->register(DoctrineSupportServiceProvider::class);

        $loader = AliasLoader::getInstance();
        $loader->alias('Iwan', IwanFacade::class);

        $this->app->singleton('iwan', function () {
            return new Iwan();
        });

        $this->loadHelpers();

        $this->registerAlertComponents();
        $this->registerFormFields();

        $this->registerConfigs();

        if ($this->app->runningInConsole()) {
            $this->registerPublishableResources();
            $this->registerConsoleCommands();
        }

        if (!$this->app->runningInConsole() || config('app.env') == 'testing') {
            $this->registerAppCommands();
        }
    }

    /**
     * Bootstrap the application services.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot(Router $router, Dispatcher $event)
    {
        if (config('iwan.user.add_default_role_on_register')) {
            $app_user = config('iwan.user.namespace') ?: config('auth.providers.users.model');
            $app_user::created(function ($user) {
                if (is_null($user->role_id)) {
                    IwanFacade::model('User')
                        ->findOrFail($user->id)
                        ->roles()
                        ->sync([config('iwan.user.default_role')]);
                }
            });
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'iwan');

        $router->aliasMiddleware('admin.user', IwanAdminMiddleware::class);

        $this->loadTranslationsFrom(realpath(__DIR__.'/../publishable/lang'), 'iwan');

        if (config('app.env') == 'testing') {
            $this->loadMigrationsFrom(realpath(__DIR__.'/migrations'));
        }

        $this->loadMigrationsFrom(realpath(__DIR__.'/../migrations'));

        $this->registerGates();

        $this->registerViewComposers();

        $event->listen('iwan.alerts.collecting', function () {
            $this->addStorageSymlinkAlert();
        });

        $this->bootTranslatorCollectionMacros();
    }

    /**
     * Load helpers.
     */
    protected function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Register view composers.
     */
    protected function registerViewComposers()
    {
        // Register alerts
        View::composer('iwan::*', function ($view) {
            $view->with('alerts', IwanFacade::alerts());
        });
    }

    /**
     * Add storage symlink alert.
     */
    protected function addStorageSymlinkAlert()
    {
        if (app('router')->current() !== null) {
            $currentRouteAction = app('router')->current()->getAction();
        } else {
            $currentRouteAction = null;
        }
        $routeName = is_array($currentRouteAction) ? array_get($currentRouteAction, 'as') : null;

        if ($routeName != 'iwan.dashboard') {
            return;
        }

        $storage_disk = (!empty(config('iwan.storage.disk'))) ? config('iwan.storage.disk') : 'public';

        if (request()->has('fix-missing-storage-symlink')) {
            if (file_exists(public_path('storage'))) {
                if (@readlink(public_path('storage')) == public_path('storage')) {
                    rename(public_path('storage'), 'storage_old');
                }
            }

            if (!file_exists(public_path('storage'))) {
                $this->fixMissingStorageSymlink();
            }
        } elseif ($storage_disk == 'public') {
            if (!file_exists(public_path('storage')) || @readlink(public_path('storage')) == public_path('storage')) {
                $alert = (new Alert('missing-storage-symlink', 'warning'))
                    ->title(__('iwan::error.symlink_missing_title'))
                    ->text(__('iwan::error.symlink_missing_text'))
                    ->button(__('iwan::error.symlink_missing_button'), '?fix-missing-storage-symlink=1');
                IwanFacade::addAlert($alert);
            }
        }
    }

    protected function fixMissingStorageSymlink()
    {
        app('files')->link(storage_path('app/public'), public_path('storage'));

        if (file_exists(public_path('storage'))) {
            $alert = (new Alert('fixed-missing-storage-symlink', 'success'))
                ->title(__('iwan::error.symlink_created_title'))
                ->text(__('iwan::error.symlink_created_text'));
        } else {
            $alert = (new Alert('failed-fixing-missing-storage-symlink', 'danger'))
                ->title(__('iwan::error.symlink_failed_title'))
                ->text(__('iwan::error.symlink_failed_text'));
        }

        IwanFacade::addAlert($alert);
    }

    /**
     * Register alert components.
     */
    protected function registerAlertComponents()
    {
        $components = ['title', 'text', 'button'];

        foreach ($components as $component) {
            $class = 'Afaneh262\\Iwan\\Alert\\Components\\'.ucfirst(camel_case($component)).'Component';

            $this->app->bind("iwan.alert.components.{$component}", $class);
        }
    }

    protected function bootTranslatorCollectionMacros()
    {
        Collection::macro('translate', function () {
            $transtors = [];

            foreach ($this->all() as $item) {
                $transtors[] = call_user_func_array([$item, 'translate'], func_get_args());
            }

            return new TranslatorCollection($transtors);
        });
    }

    /**
     * Register the publishable files.
     */
    private function registerPublishableResources()
    {
        $publishablePath = dirname(__DIR__).'/publishable';

        $publishable = [
            'iwan_assets' => [
                "{$publishablePath}/assets/" => public_path(config('iwan.assets_path')),
            ],
            'seeds' => [
                "{$publishablePath}/database/seeds/" => database_path('seeds'),
            ],
            'config' => [
                "{$publishablePath}/config/iwan.php" => config_path('iwan.php'),
            ],

        ];

        foreach ($publishable as $group => $paths) {
            $this->publishes($paths, $group);
        }
    }

    public function registerConfigs()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/publishable/config/iwan.php', 'iwan'
        );
    }

    public function registerGates()
    {
        // This try catch is necessary for the Package Auto-discovery
        // otherwise it will throw an error because no database
        // connection has been made yet.
        try {
            if (Schema::hasTable('data_types')) {
                $dataType = IwanFacade::model('DataType');
                $dataTypes = $dataType->select('policy_name', 'model_name')->get();

                foreach ($dataTypes as $dataType) {
                    $policyClass = BasePolicy::class;
                    if (isset($dataType->policy_name) && $dataType->policy_name !== ''
                        && class_exists($dataType->policy_name)) {
                        $policyClass = $dataType->policy_name;
                    }

                    $this->policies[$dataType->model_name] = $policyClass;
                }

                $this->registerPolicies();
            }
        } catch (\PDOException $e) {
            Log::error('No Database connection yet in IwanServiceProvider registerGates()');
        }
    }

    protected function registerFormFields()
    {
        $formFields = [
            'checkbox',
            'color',
            'date',
            'file',
            'image',
            'multiple_images',
            'number',
            'password',
            'radio_btn',
            'rich_text_box',
            'code_editor',
            'markdown_editor',
            'select_dropdown',
            'select_multiple',
            'text',
            'text_area',
            'time',
            'timestamp',
            'hidden',
            'coordinates',
        ];

        foreach ($formFields as $formField) {
            $class = studly_case("{$formField}_handler");

            IwanFacade::addFormField("Afaneh262\\Iwan\\FormFields\\{$class}");
        }

        IwanFacade::addAfterFormField(DescriptionHandler::class);

        event(new FormFieldsRegistered($formFields));
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(Commands\InstallCommand::class);
        $this->commands(Commands\ControllersCommand::class);
        $this->commands(Commands\AdminCommand::class);
    }

    /**
     * Register the commands accessible from the App.
     */
    private function registerAppCommands()
    {
        $this->commands(Commands\MakeModelCommand::class);
    }
}
