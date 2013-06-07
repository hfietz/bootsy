<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\View\MenuItem;

class MenuManager
{
  const CLIENT_INTERFACE_NAME = 'Econemon\Bootsy\ApplicationBundle\Service\MenuAware';
  const SETTER_NAME = 'setMenuManager';
  const SERVICE_ID = 'econemon_bootsy_menu';

  /**
   * @var MenuItem
   */

  protected $menu = NULL;

  public function __construct($rootLabel = 'Home', $rootTarget = NULL)
  {
    $this->menu = new MenuItem($rootLabel, $rootTarget);
  }

  /**
   * @return mixed
   */
  public function getMenu()
  {
    return $this->menu;
  }

  public function addMenuItem(MenuItem $item)
  {
    $this->menu->addChildItem($item);

    return $this;
  }

  public function createMenuItem($label, $target = NULL)
  {
    $item = new MenuItem($label, $target);
    $this->addMenuItem($item);

    return $item;
  }
}