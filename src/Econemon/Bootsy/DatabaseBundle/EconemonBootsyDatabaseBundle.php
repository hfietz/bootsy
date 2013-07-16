<?php
namespace Econemon\Bootsy\DatabaseBundle;

use Econemon\Bootsy\ApplicationBundle\DependencyInjection\ImplementationDetectorCompilerPass;
use Econemon\Bootsy\DatabaseBundle\Service\DatabaseService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EconemonBootsyDatabaseBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $pass = new ImplementationDetectorCompilerPass();

    $pass
      ->service(DatabaseService::SERVICE_ID)
      ->catersFor(DatabaseService::CLIENT_INTERFACE_NAME)
      ->via(DatabaseService::SET_DATABASE_SERVICE);

    $pass
      ->service(DatabaseService::SERVICE_ID)
      ->consumes(DatabaseService::EXTENDER_INTERFACE_NAME)
      ->via(DatabaseService::REGISTRATION_CALLBACK);

    $container->addCompilerPass($pass);
  }

}