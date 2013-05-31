<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\View\MenuItem;

class MenuManager
{
  /**
   * @var MenuItem[]
   */

  protected $menu = array();

  /**
   * @return mixed
   */
  public function getMenu()
  {
    return $this->menu;
  }

  public function addMenuItem(MenuItem $item)
  {
    $this->menu[] = $item;

    return $this;
  }

  public function createMenuItem($label, $target = NULL)
  {
    $item = new MenuItem($label, $target);
    $this->addMenuItem($item);

    return $item;
  }
}