<?php
namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Model\ConfigItem;
use Econemon\Bootsy\ApplicationBundle\Model\ConfigItemMapper;

class ConfigManager extends BaseService
{
  const SERVICE_ID = 'econemon_bootsy_config';

  const CLIENT_IFACE = 'Econemon\Bootsy\ApplicationBundle\Service\ConfigAware';

  const CLIENT_SETTER = 'setConfigManager';

  /**
   * @var ConfigItem[]
   */
  protected $items = NULL;

  public function get($name, &$found = NULL)
  {
    if (NULL === $this->items) {
      $this->items = $this->load();
    }

    $value = NULL;

    if (array_key_exists($name, $this->items)) {
      $value = $this->items[$name];
      $found = TRUE;
    } else {
      $found = FALSE;
    }

    return $value;
  }

  public function load($name = NULL)
  {
    return $this->databaseService->load(new ConfigItemMapper($name), 'machine_name');
  }

}