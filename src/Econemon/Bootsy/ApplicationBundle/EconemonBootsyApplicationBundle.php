<?php

namespace Econemon\Bootsy\ApplicationBundle;

use Econemon\Bootsy\ApplicationBundle\DependencyInjection\MenuCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EconemonBootsyApplicationBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    parent::build($container);

    $container->addCompilerPass(new MenuCompilerPass());
  }

}