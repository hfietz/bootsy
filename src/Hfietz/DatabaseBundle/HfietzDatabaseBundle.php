<?php
namespace Hfietz\DatabaseBundle;

use Hfietz\DatabaseBundle\DependencyInjection\DatabaseServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HfietzDatabaseBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new DatabaseServiceCompilerPass());
  }

}