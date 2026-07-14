<?php

namespace Eddyter\Laravel;

use Eddyter\Laravel\Commands\InstallCommand;
use Eddyter\Laravel\View\Components\Editor;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class EddyterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/eddyter.php',
            'eddyter'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'eddyter'
        );

        Blade::component('eddyter', Editor::class);
        
        $this->registerBladeDirectives();

        $this->registerPublishing();

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('eddyterStyles', function () {
            return "<?php echo \$__env->make('eddyter::assets.styles', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });

        Blade::directive('eddyterScripts', function () {
            return "<?php echo \$__env->make('eddyter::assets.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });

        Blade::directive('eddyterAssets', function () {
            return "<?php echo \$__env->make('eddyter::assets.all', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/eddyter.php' => config_path('eddyter.php'),
        ], 'eddyter-config');

        $this->publishes([
            __DIR__ . '/../resources/dist/eddyter-laravel.js' => public_path('vendor/eddyter/eddyter-laravel.js'),
        ], 'eddyter-assets');
    }
}
