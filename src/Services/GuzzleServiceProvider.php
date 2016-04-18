<?php
namespace Newsletter\Services;

use Silex\Application;
use Silex\ServiceProviderInterface;
use GuzzleHttp\Client;

class GuzzleServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['guzzle.options_default'] = [
            'timeout' => 3
        ];

        // Global guzzle service
        $app['guzzle'] = $app->share(function () use ($app) {
            if (isset($app['guzzle.options'])) {
                $options = array_merge($app['guzzle.options_default'], $app['guzzle.options']);
            }
            else {
                $options = $app['guzzle.options_default'];
            }

            return new Client($options);
        });

        // Init a new guzzle instance
        $app['new_guzzle'] = $app->protect(function ($options) {
            return new Client($options);
        });
    }

    public function boot(Application $app)
    {
    }
}
