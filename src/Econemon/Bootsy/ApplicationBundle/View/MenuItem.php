<?php

namespace Econemon\Bootsy\ApplicationBundle\View;

use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;

class MenuItem
{
  const LEVEL_ROOT = 0;
  /**
   * @var string the name of a route, to be used with Twig's path() helper
   */
  protected $target;

  /**
   * @var string the string to be displayed to the user
   */
  protected $label;

  /**
   * @var MenuItem[] subitems
   */
  protected $children = array();

  /**
   * @var int
   */
  protected $level = self::LEVEL_ROOT;

  /**
   * @var array
   */
  protected $roles = array();

  /**
   * @param string $label
   * @param string $target
   */
  public function __construct($label, $target = NULL, $level = self::LEVEL_ROOT)
  {
    if (!is_string($label)) {
      throw DefensiveCodeException::forUnexpectedTypeOf($label, 'string');
    }
    if (NULL !== $target && !is_string($target)) {
      throw DefensiveCodeException::forUnexpectedTypeOf($target, 'string');
    }
    $this->target = $target;
    $this->label = $label;

    $this->roles[] = 'ROLE_USER';
  }

  /**
   * @return string
   */
  public function getTarget()
  {
    return $this->target;
  }

  /**
   * @return string
   */
  public function getLabel()
  {
    return empty($this->label) ? ucwords(str_replace(array('-', '_', '/'), ' ', $this->target)) : $this->label;
  }

  /**
   * @return MenuItem[]
   */
  public function getChildren()
  {
    return $this->children;
  }

  public function addChild($label, $target = NULL)
  {
    $child = new MenuItem($label, $target);

    return $this->addChildItem($child);
  }

  public function hasTarget()
  {
    return !empty($this->target);
  }

  /**
   * @return int
   */
  public function getLevel()
  {
    return $this->level;
  }

  public function hasChildren()
  {
    return count($this->children) > 0;
  }

  /**
   * @param MenuItem $child
   */
  public function addChildItem(MenuItem $child)
  {
    $child->level = $this->level + 1;

    $this->children[] = $child;

    return $child;
  }

  /**
   * @param string $label
   * @return bool
   */
  public function hasChildWithLabel($label)
  {
    return NULL !== $this->getChildWithLabel($label);
  }

  /**
   * @param string $label
   * @return MenuItem|null
   */
  public function getChildWithLabel($label)
  {
    $result = NULL;

    foreach ($this->children as $child) {
      if ($child->getLabel() === $label) {
        $result = $child;
        break;
      }
    }

    return $result;
  }

  public function cloneFlat($level = NULL)
  {
    return new MenuItem($this->label, $this->target, NULL === $level ? $this->level : $level);
  }

  public function asEmptyRoot()
  {
    return $this->cloneFlat(self::LEVEL_ROOT);
  }

  /**
   * @return array
   */
  public function getRoles()
  {
    return $this->roles;
  }
}