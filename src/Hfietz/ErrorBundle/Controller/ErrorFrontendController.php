<?php

namespace Hfietz\ErrorBundle\Controller;

use Hfietz\ErrorBundle\Model\ErrorView;
use Hfietz\ErrorBundle\Service\ErrorService;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class ErrorFrontendController
{
  /**
   * @var ErrorService
   */
  protected $errorService;

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
    $list = array();
    foreach ($this->errorService->loadErrors() as $error) {
      $list[] = ErrorView::fromLoggedException($error);
    }

    $view = array(
      'pageTitle' => 'Error Log',
      'list' => $list,
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

  /**
   * @param \Hfietz\ErrorBundle\Service\ErrorService $errorService
   */
  public function setErrorService($errorService)
  {
    $this->errorService = $errorService;
  }
}