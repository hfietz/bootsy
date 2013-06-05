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
}
