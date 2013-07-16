<?php

namespace Econemon\Bootsy\ApplicationBundle;

use Econemon\Bootsy\ApplicationBundle\DependencyInjection\ImplementationDetectorCompilerPass;
use Econemon\Bootsy\ApplicationBundle\Service\ConfigManager;
use Econemon\Bootsy\ApplicationBundle\Service\DoctrineAware;
use Econemon\Bootsy\ApplicationBundle\Service\FormFactoryAware;
use Econemon\Bootsy\ApplicationBundle\Service\MenuManager;
use Econemon\Bootsy\ApplicationBundle\Service\SecurityContextAware;
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

    $pass->service(SecurityContextAware::EXPECTED_SERVICE_ID)->catersFor(SecurityContextAware::CLIENT_IFACE)->via(SecurityContextAware::SETTER_NAME);

    $pass->service(DoctrineAware::DOCTRINE_SERVICE_ID)->catersFor(DoctrineAware::DOCTRINE_CLIENT_IFACE)->via(DoctrineAware::SET_DOCTRINE);

    $pass->service(FormFactoryAware::FORM_FACTORY_SERVICE_ID)->catersFor(FormFactoryAware::FORM_FACTORY_CLIENT_IFACE)->via(FormFactoryAware::FORM_FACTORY_SETTER_NAME);

    $container->addCompilerPass($pass);
  }

}