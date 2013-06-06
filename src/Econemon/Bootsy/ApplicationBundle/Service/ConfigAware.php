<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

interface ConfigAware
{
  public function setConfigManager(ConfigManager $configManager);
}