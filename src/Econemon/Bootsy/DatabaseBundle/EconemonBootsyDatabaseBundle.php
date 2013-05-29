<?php
namespace Econemon\Bootsy\DatabaseBundle;

use Econemon\Bootsy\DatabaseBundle\DependencyInjection\DatabaseServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EconemonBootsyDatabaseBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new DatabaseServiceCompilerPass());
  }

}