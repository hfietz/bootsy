<?php

namespace Econemon\Bootsy\ApplicationBundle\View;

class MenuItem
{
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
  protected $level = 0;

  /**
   * @param string $label
   * @param string $target
   */
  public function __construct($label, $target = NULL, $level = 0)
  {
    $this->target = $target;
    $this->label = $label;
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
    $child = new MenuItem($label, $target, $this->level + 1);

    $this->children[] = $child;

    return $child;
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
}