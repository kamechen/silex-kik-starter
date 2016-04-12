<?php
namespace Newsletter\Controllers;

use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index()
    {
        return $this->app->render("home.html.twig");
    }
}
