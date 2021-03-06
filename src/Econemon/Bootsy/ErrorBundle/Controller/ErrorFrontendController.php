<?php

namespace Econemon\Bootsy\ErrorBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Controller\BaseController;
use Econemon\Bootsy\ApplicationBundle\Service\MenuExtender;
use Econemon\Bootsy\ErrorBundle\Model\ErrorView;
use Econemon\Bootsy\ErrorBundle\Service\ErrorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ErrorFrontendController extends BaseController implements MenuExtender
{
  /**
   * @var ErrorService
   */
  protected $errorService;

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

  public function testAction($code = NULL) {
    switch ($code) {
      case "404":
        throw new NotFoundHttpException('Testing 404 page');
        break;
      case "500":
        throw new HttpException(500, 'Testing 500 page');
        break;
      default:
        throw new HttpException(501, 'Testing unknown error page');
        break;
    }
  }

  /**
   * @param \Econemon\Bootsy\ErrorBundle\Service\ErrorService $errorService
   */
  public function setErrorService($errorService)
  {
    $this->errorService = $errorService;
  }

  public function getMenuDescription()
  {
    return array(
      'menu.system._section' => array(
        'menu.system.errors' => 'error_list',
      ),
      'menu.test._section' => array(
        'menu.test.error-pages._section' => array(
          'menu.test.error-pages.404' => 'test_404',
          'menu.test.error-pages.500' => 'test_500',
          'menu.test.error-pages.general' => 'test_error',
        ),
      ),
    );
  }
}