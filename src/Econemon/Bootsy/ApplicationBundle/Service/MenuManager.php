<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;
use Econemon\Bootsy\ApplicationBundle\View\MenuItem;

class MenuManager
{
  const CLIENT_INTERFACE_NAME = 'Econemon\Bootsy\ApplicationBundle\Service\MenuAware';
  const EXTENDER_INTERFACE_NAME = 'Econemon\Bootsy\ApplicationBundle\Service\MenuExtender';

  const SETTER_NAME = 'setMenuManager';
  const REGISTRATION_CALLBACK = 'registerMenuExtension';

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
   * @return MenuItem
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

  public function registerMenuExtension(MenuExtender $extender)
  {
    $parent = $this->menu;
    $menuDescription = $extender->getMenuDescription();
    self::parseMenuDescription($menuDescription, $parent);
  }

  /**
   * @param array $menuDescription
   * @param MenuItem $parent
   * @throws \Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException
   * @throws \Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException
   */
  protected static function parseMenuDescription(array $menuDescription, MenuItem $parent)
  {
    foreach ($menuDescription as $label => $item) {
      $target = NULL;
      $children = array();

      if (is_string($item)) {
        $target = $item;
      } else if (is_array($item)) {
        // We use one required key as indicator for the existence of explicit values
        if (array_key_exists('target', $item)) {
          $target = $item['target'];
          if (array_key_exists('label', $item)) {
            $label = $item['label'];
          }
          if (array_key_exists('children', $item)) {
            if (!is_array($item['children'])) {
              // better than a catchable fatal.
              throw DefensiveCodeException::forUnexpectedTypeOf($item['children'], 'array');
            }
            $children = $item['children'];
          }
        } else {
          $children = $item;
        }
      } else {
        throw DefensiveCodeException::forUnexpectedTypeOf($item, 'string|array');
      }

      $child = $parent->getChildWithLabel($label);
      if (NULL === $child) {
        $child = $parent->addChild($label, $target);
      }

      self::parseMenuDescription($children, $child);
    }
  }
}