<?php
namespace Newsletter\Services;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SendgridServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['sendgrid'] = $app->share(function() use ($app) {
            $options = [];

            if (isset($app['sendgrid.options'])) {
                $options = $app['sendgrid.options'];
            }
            return new \SendGrid($app['sendgrid.apikey'], $options);
        });

        $app['sendgrid.email'] = function() {
            return new \SendGrid\Email();
        };

        $app['new_sendgrid'] = $app->protect(function ($options) use($app) {
            return new \SendGrid($app['sendgrid.apikey'], $options);
        });
    }

    public function boot(Application $app)
    {
    }
}
