<?php

namespace Econemon\Bootsy\ApplicationBundle\Model;

class ConfigItem
{
  public $machineName;

  public $displayText;

  public $value;

  public static function fromData($data)
  {
    $config = new ConfigItem();

    $config->displayText = $data['display_text'];
    $config->machineName = $data['machine_name'];
    $config->value = $data['value'];

    return $config;
  }
}