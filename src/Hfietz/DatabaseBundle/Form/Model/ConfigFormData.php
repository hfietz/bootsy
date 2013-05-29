<?php

namespace Hfietz\DatabaseBundle\Form\Model;

use Hfietz\DatabaseBundle\Model\DatabaseConfiguration;
use Symfony\Component\Validator\ExecutionContextInterface;

class ConfigFormData
{
  public $driver;

  public $host;

  public $name;

  public $user;

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
    $formData->driver = $config->driverName;
    $formData->name = $config->databaseName;
    $formData->host = $config->host;
    $formData->user = $config->user;
    // The existing password is not part of the form.
    
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
    if (!empty($this->newPassword)) {
      $config->password = $this->newPassword;
    }

    return $config;
  }

  public function checkNewPasswordForTypos(ExecutionContextInterface $context)
  {
    if (!empty($this->newPassword) && $this->newPassword != $this->newPasswordRepeat) {
      $context->addViolationAt('newPasswordRepeat', 'You have to enter the new password twice to avoid typos. The two values you entered did not match.');
    }
  }
}