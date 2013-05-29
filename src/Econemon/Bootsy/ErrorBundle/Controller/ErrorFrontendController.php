<?php

namespace Econemon\Bootsy\ErrorBundle\Controller;

use Econemon\Bootsy\ErrorBundle\Model\ErrorView;
use Econemon\Bootsy\ErrorBundle\Service\ErrorService;
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
    return $this->templateEngine->renderResponse('EconemonBootsyErrorBundle:ErrorFrontend:wall_of_shame.html.twig', $view);
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
   * @param \Econemon\Bootsy\ErrorBundle\Service\ErrorService $errorService
   */
  public function setErrorService($errorService)
  {
    $this->errorService = $errorService;
  }
}