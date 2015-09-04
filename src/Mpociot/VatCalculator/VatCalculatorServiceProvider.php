<?php namespace Mpociot\VatCalculator;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Mpociot\VatCalculator\Facades\VatCalculator;
use Mpociot\VatCalculator\Validators\VatCalculatorValidatorExtension;

class VatCalculatorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->registerValidatorExtension();
    }

    /**
     * Publish Teamwork configuration
     */
    protected function publishConfig()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('vat_calculator.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerVatCalculator();
        $this->registerFacade();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerVatCalculator()
    {
        $this->app->bind('vatcalculator', function ($app) {
            return new VatCalculator($app);
        });
    }

    /**
     * Register the vault facade without the user having to add it to the app.php file.
     *
     * @return void
     */
    public function registerFacade()
    {
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('VatCalculator', 'Mpociot\VatCalculator\Facades\VatCalculator');
        });
    }

    /**
     * Merges user's and teamwork's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/config.php', 'vat_calculator'
        );
    }

    protected function registerValidatorExtension()
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../../lang',
            'vatnumber-validator'
        );

        // Registering the validator extension with the validator factory
        $this->app['validator']->resolver(
            function($translator, $data, $rules, $messages, $customAttributes = array())
            {
                return new VatCalculatorValidatorExtension(
                    $translator,
                    $data,
                    $rules,
                    $messages,
                    $customAttributes
                );
            }
        );
    }
}
