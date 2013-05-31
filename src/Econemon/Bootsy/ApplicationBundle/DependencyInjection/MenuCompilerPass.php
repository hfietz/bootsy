<?php

namespace Econemon\Bootsy\ApplicationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MenuCompilerPass implements CompilerPassInterface
{
  const CLIENT_INTERFACE_NAME = 'Econemon\Bootsy\ApplicationBundle\Service\MenuAware';
  const SETTER_NAME = 'setMenuManager';
  const SERVICE_ID = 'menu_service';

  /**
   * You can modify the container here before it is dumped to PHP code.
   *
   * @param ContainerBuilder $container
   *
   * @api
   */
  public function process(ContainerBuilder $container)
  {
      foreach ($container->getDefinitions() as $serviceId => $definition) {
        if (is_subclass_of($definition->getClass(), self::CLIENT_INTERFACE_NAME)) {
          $definition->addMethodCall(self::SETTER_NAME, array(new Reference(self::SERVICE_ID)));
        }
      }
  }
}