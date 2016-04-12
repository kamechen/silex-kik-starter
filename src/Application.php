<?php
namespace Newsletter;

use Silex\Application as SilexApplication;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpCacheServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Application extends SilexApplication
{
    use SilexApplication\TwigTrait;
    use SilexApplication\FormTrait;
    use SilexApplication\UrlGeneratorTrait;
    use SilexApplication\SwiftmailerTrait;
    use SilexApplication\MonologTrait;
    use SilexApplication\TranslationTrait;

    private $rootDir;
    private $env;

    public function __construct($env)
    {
        $this->rootDir = __DIR__ . '/../';
        $this->env = $env;

        parent::__construct();

        $app = $this;

        // Override these values in resources/config/[env].php file
        $app['var_dir'] = $this->rootDir . '/var';
        $app['locale'] = 'en';
        $app['http_cache.cache_dir'] = $app->share(function (Application $app) {
            return $app['var_dir'] . '/http';
        });

        // monolog params
        $app['monolog.logfile'] = $app['var_dir'] . "/logs/{$env}.log";
        $app['monolog.name'] = $env;
        $app['monolog.level'] = 300; // = Logger::WARNING

        $configFile = sprintf('%sresources/config/%s.php', $this->rootDir, $env);
        if (!file_exists($configFile)) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $configFile));
        }
        require $configFile;

        $app->register(new HttpCacheServiceProvider());
        $app->register(new SessionServiceProvider());
        $app->register(new ValidatorServiceProvider());
        $app->register(new FormServiceProvider());
        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new DoctrineServiceProvider());
        $app->register(new TranslationServiceProvider());

        $app['translator'] = $app->share($app->extend('translator', function ($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', $this->rootDir . '/resources/locales/en.yml', 'en');

            return $translator;
        }));

        $app->register(new MonologServiceProvider());
        $app->register(new TwigServiceProvider(), [
            'twig.options' => [
                'cache' => $app['var_dir'] . '/cache/twig',
                'strict_variables' => true,
            ],
            'twig.form.templates' => ['bootstrap_3_horizontal_layout.html.twig'],
            'twig.path' => [$this->rootDir . '/views'],
        ]);

        $app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
            $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
                $base = $app['request_stack']->getCurrentRequest()->getBasePath();

                return sprintf($base . '/' . $asset, ltrim($asset, '/'));
            }));

            return $twig;
        }));

        $this->register(new ServiceControllerServiceProvider());

        $this->loadRoutes();
        $this->loadServices();
    }

    public function getRootDir()
    {
        return $this->rootDir;
    }

    public function getEnv()
    {
        return $this->env;
    }

    private function loadRoutes()
    {
        $app = $this;

        //accepting JSON
        $this->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace(is_array($data) ? $data : array());
            }
        });

        $this["home.controller"] = $this->share(function () use($app) {
            return new Controllers\HomeController($app);
        });

        $this->get('/', "home.controller:index")->bind('home');
    }

    private function loadServices()
    {

    }
}
