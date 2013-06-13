<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\MenuAware;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class MenuController implements MenuAware
{
  /**
   * @var SecurityContextInterface
   */
  protected $securityContext;

  /**
   * @var MenuManager
   */
  protected $menuManager;

  /**
   * @var EngineInterface
   */
  protected $templateEngine;

  public function menuAction(Request $req)
  {
    return $this->templateEngine->renderResponse('EconemonBootsyApplicationBundle:Main:menu.html.twig', array('menu' => $this->menuManager->getMenu()));
  }

  /**
   * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templateEngine
   */
  public function setTemplateEngine($templateEngine)
  {
    $this->templateEngine = $templateEngine;
  }

  /**
   * @param \Econemon\Bootsy\ApplicationBundle\Service\MenuManager $menuManager
   */
  public function setMenuManager(MenuManager $menuManager)
  {
    $this->menuManager = $menuManager;
  }

  /**
   * @param \Symfony\Component\Security\Core\SecurityContextInterface $securityContext
   */
  public function setSecurityContext($securityContext)
  {
    $this->securityContext = $securityContext;
  }
}