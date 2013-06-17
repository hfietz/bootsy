<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\MenuAware;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;
use Econemon\Bootsy\ApplicationBundle\View\MenuItem;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\FirewallMapInterface;

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

  /**
   * @var Router
   */
  protected $router;

  /**
   * @var AccessMapInterface
   */
  protected $accessMap;

  /**
   * @var AccessDecisionManagerInterface
   */
  protected $accessDecisionManager;

  public function menuAction(Request $req)
  {
    $menu = $this->getFilteredMenu();

    return $this->templateEngine->renderResponse('EconemonBootsyApplicationBundle:Main:menu.html.twig', array('menu' => $menu));
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

  /**
   * @return \Econemon\Bootsy\ApplicationBundle\View\MenuItem
   */
  protected function getFilteredMenu()
  {
    $menu = $this->menuManager->getMenu()->asEmptyRoot();

    $this->filterNode($this->menuManager->getMenu(), $menu);

    return $menu;
  }

  /**
   * @param MenuItem $unfilteredNode
   * @param MenuItem $filteredNode
   */
  protected function filterNode($unfilteredNode, $filteredNode)
  {
    $hasGrantedChildren = FALSE;

    foreach ($unfilteredNode->getChildren() as $child) {
      $filteredChild = $child->cloneFlat();
      $target = $child->getTarget();

      if (NULL !== $target) {
        $granted = $this->currentUserHasAccessTo($target);
        if ($granted) {
          $this->filterNode($child, $filteredChild);
        }
      } else {
        $granted = $this->filterNode($child, $filteredChild);
      }

      if ($granted) {
        $filteredNode->addChildItem($filteredChild);
      }

      $hasGrantedChildren = $hasGrantedChildren || $granted;
    }

    return $hasGrantedChildren;
  }

  /**
   * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
   */
  public function setRouter($router)
  {
    $this->router = $router;
  }

  /**
   * @param \Symfony\Component\Security\Http\AccessMapInterface $accessMap
   */
  public function setAccessMap($accessMap)
  {
    $this->accessMap = $accessMap;
  }

  /**
   * @param \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $accessDecisionManager
   */
  public function setAccessDecisionManager($accessDecisionManager)
  {
    $this->accessDecisionManager = $accessDecisionManager;
  }

  /**
   * @param $target
   * @return bool
   */
  protected function currentUserHasAccessTo($target)
  {
    $route = $this->router->getRouteCollection()->get($target);
    if (NULL === $route) {
      return FALSE;
    }
    $path = $route->getPath();
    $request = Request::create($path);
    $tuple = $this->accessMap->getPatterns($request);
    $attributes = $tuple[0];
    $token = $this->securityContext->getToken();
    $granted = NULL === $attributes || ($token->isAuthenticated() && $this->accessDecisionManager->decide($token, $attributes, $request));
    return $granted;
  }
}