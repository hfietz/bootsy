<?php

namespace Hfietz\ErrorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class ErrorFrontendController
{
  /**
   * @var EngineInterface
   */
  protected $templateEngine;

  /**
   * @var Router
   */
  protected $router;

  public function listAction()
  {
    return new Response('<h1>Wall Of Shame</h1>');
  }

  /**
   * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templateEngine
   */
  public function setTemplateEngine($templateEngine)
  {
    $this->templateEngine = $templateEngine;
  }

  /**
   * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
   */
  public function setRouter($router)
  {
    $this->router = $router;
  }
}