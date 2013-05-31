<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

interface MenuAware
{
  public function setMenuManager(MenuManager $menuManager);
}