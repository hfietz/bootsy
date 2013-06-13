<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\MenuAware;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;

use Econemon\Bootsy\ApplicationBundle\View\MainView;
use Econemon\Bootsy\ApplicationBundle\View\MenuItem;
use Exception;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class MainController implements MenuAware
{
  /**
   * @var MenuManager
   */
  protected $menuManager;

  /**
   * @var EngineInterface
   */
  protected $templateEngine;

  public function indexAction()
  {
    $view = $this->initializeMainView();

    return $this->render('EconemonBootsyApplicationBundle:Main:index.html.twig', array('view' => $view));
  }

  /**
   * @return MainView
   */
  protected function initializeMainView()
  {
    $view = new MainView();

    $view->setPageTitle('Bootsy');
    $view->setClaimHeadline('Bootsy Web Application');

    return $view;
  }

  public function render($view, $parameters)
  {
    if (NULL !== $this->templateEngine) {
      return $this->templateEngine->renderResponse($view, $parameters);
    } else {
      throw new Exception('Dependency injection failed: missing template engine.');
    }
  }

  /**
   * @param \Econemon\Bootsy\ApplicationBundle\Service\MenuManager $menuManager
   */
  public function setMenuManager(MenuManager $menuManager)
  {
    $this->menuManager = $menuManager;
  }

  /**
   * @param mixed $templateEngine
   */
  public function setTemplateEngine($templateEngine)
  {
    $this->templateEngine = $templateEngine;
  }
}
