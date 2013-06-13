<?php

namespace Econemon\Bootsy\ApplicationBundle;

use Econemon\Bootsy\ApplicationBundle\DependencyInjection\ImplementationDetectorCompilerPass;
use Econemon\Bootsy\ApplicationBundle\Service\ConfigManager;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EconemonBootsyApplicationBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $pass = new ImplementationDetectorCompilerPass();

    $pass->service(MenuManager::SERVICE_ID)->catersFor(MenuManager::CLIENT_INTERFACE_NAME)->via(MenuManager::SETTER_NAME);
    $pass->service(MenuManager::SERVICE_ID)->consumes(MenuManager::EXTENDER_INTERFACE_NAME)->via(MenuManager::REGISTRATION_CALLBACK);

    $pass->service(ConfigManager::SERVICE_ID)->catersFor(ConfigManager::CLIENT_IFACE)->via(ConfigManager::CLIENT_SETTER);

    $container->addCompilerPass($pass);
  }

}