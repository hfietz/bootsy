<?php

namespace Hfietz\DatabaseBundle\Form\Model;

class ConfigFormData
{
  public $driver;
  public $driverPrevious;

  public $host;
  public $hostPrevious;

  public $name;
  public $namePrevious;

  public $user;
  public $userPrevious;

  public $currentPassword;
  public $newPassword;
  public $newPasswordRepeat;

  // this is a hack I do when I start to miss Java OOP
  public static function getClassName()
  {
    return __CLASS__;
  }
}