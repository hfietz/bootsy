<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\MenuAware;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;

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
    $view = array();

    if (NULL !== $this->menuManager) {
      $menu = $this->menuManager->createMenuItem('System');
      $menu->addChild('Wall of Shame', 'error_list');
      $menu->addChild('DB status and config', 'db_status');
      $menu->addChild('DB versions', 'db_versions');

      $menu = $this->menuManager->createMenuItem('Error pages');
      $menu->addChild('Not found', 'test_404');
      $menu->addChild('Server error', 'test_500');
      $menu->addChild('Unknown error', 'test_error');

      $view['menu'] = $this->menuManager->getMenu();
    }

    return $this->render('EconemonBootsyApplicationBundle:Main:index.html.twig', $view);
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
