<?php

namespace Econemon\Bootsy\ApplicationBundle;

use Econemon\Bootsy\ApplicationBundle\DependencyInjection\ImplementationDetectorCompilerPass;
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

    $container->addCompilerPass($pass);
  }

}