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
    $view = array(
      'pageTitle' => 'Error Log',
    );
    return $this->templateEngine->renderResponse('HfietzErrorBundle:ErrorFrontend:wall_of_shame.html.twig', $view);
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