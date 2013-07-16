<?php

namespace Econemon\Bootsy\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

/**
 * Class User
 * @package Econemon\Bootsy\ApplicationBundle\Entity
 *
 */
class User extends BaseUser
{
  /**
   * @var int
   */
  protected $id;

  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $phone;

  /**
   * @param mixed $object
   * @return bool
   */
  public static function isSameClass($object)
  {
    return is_a($object, __CLASS__);
  }

  public function getDisplayName()
  {
    return NULL === $this->getName() ? $this->getUsername() : $this->getName();
  }

  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getPhone()
  {
    return $this->phone;
  }

  /**
   * @param string $phone
   */
  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  /**
   * @param mixed $other
   * @return bool
   */
  public function hasSameIdentity($other)
  {
    return is_a($other, get_class($this)) && $this->hasIdentity() && $this->getId() === $other->getId();
  }

  /**
   * @return bool
   */
  public function hasIdentity()
  {
    return NULL !== $this->getId();
  }
}
