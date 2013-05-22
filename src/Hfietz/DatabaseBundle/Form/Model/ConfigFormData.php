<?php

namespace Hfietz\DatabaseBundle\Form\Model;

use Hfietz\DatabaseBundle\Model\DatabaseConfiguration;

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

  /**
   * @param DatabaseConfiguration $config
   * @return ConfigFormData|null
   */
  public static function fromConfig(DatabaseConfiguration $config = NULL)
  {
    if (NULL === $config) {
      return NULL;
    }
    
    $formData = new ConfigFormData();
    $formData->driverPrevious = $formData->driver = $config->driverName;
    $formData->namePrevious = $formData->name = $config->databaseName;
    $formData->hostPrevious = $formData->host = $config->host;
    $formData->userPrevious = $formData->user = $config->user;
    
    return $formData;
  }

  /**
   * @return DatabaseConfiguration
   */
  public function toConfig()
  {
    $config = new DatabaseConfiguration();

    $config->driverName = $this->driver;
    $config->databaseName = $this->name;
    $config->host = $this->host;
    $config->user = $this->user;
    
    return $config;
  }
}