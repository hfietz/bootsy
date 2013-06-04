<?php

namespace Econemon\Bootsy\DatabaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DatabaseServiceCompilerPass implements CompilerPassInterface
{
  const CLIENT_INTERFACE_NAME = 'Econemon\Bootsy\DatabaseBundle\Service\DatabaseServiceAware';
  const PROVIDER_INTERFACE_NAME = 'Econemon\Bootsy\DatabaseBundle\Service\DatabaseUpdateProvider';
  const SETTER_NAME = 'setDatabaseService';
  const SERVICE_ID = 'econemon_bootsy_database';
  const REGISTRATION_CALLBACK = 'registerSchemaProviderService';

  /**
   * You can modify the container here before it is dumped to PHP code.
   *
   * @param ContainerBuilder $container
   *
   * @api
   */
  public function process(ContainerBuilder $container)
  {
    if ($container->hasDefinition(self::SERVICE_ID)) {
      $db_service_definition = $container->findDefinition(self::SERVICE_ID);

      foreach ($container->getDefinitions() as $serviceId => $definition) {
        if (is_subclass_of($definition->getClass(), self::CLIENT_INTERFACE_NAME)) {
          $definition->addMethodCall(self::SETTER_NAME, array(new Reference(self::SERVICE_ID)));
        }
        if (is_subclass_of($definition->getClass(), self::PROVIDER_INTERFACE_NAME)) {
          $db_service_definition->addMethodCall(self::REGISTRATION_CALLBACK, array($serviceId));
        }
      }
    }
  }
}