<?php

namespace Econemon\Bootsy\ApplicationBundle\DependencyInjection;

use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;
use Exception;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImplementationDetectorCompilerPass implements CompilerPassInterface
{
  /**
   * @var object
   */
  protected $constructionSite;

  /**
   * @var ServiceInterfaceDescription[]
   */
  protected $consumers = array();

  /**
   * @var ServiceInterfaceDescription[]
   */
  protected $providers = array();

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
      foreach ($this->consumers as $consumer) {
        $isProvider = is_subclass_of($definition->getClass(), $consumer->getInterfaceName());
        $consumerExists = $container->hasDefinition($consumer->getServiceId());
        if ($consumerExists && $isProvider) {
          $container->findDefinition($consumer->getServiceId())->addMethodCall($consumer->getRegistrationMethod(), array(new Reference($serviceId)));
        }
      }

      foreach ($this->providers as $provider) {
        $isClient = is_subclass_of($definition->getClass(), $provider->getInterfaceName());
        $providerExists = $container->hasDefinition($provider->getServiceId());
        if ($isClient && $providerExists) {
          $definition->addMethodCall($provider->getRegistrationMethod(), array(new Reference($provider->getServiceId())));
        }
      }
    }
  }

  public function service($id)
  {
    $this->constructionSite = (object)array(
      'service' => $id,
      'iface' => NULL,
      'method' => NULL,
      'type' => NULL,
    );

    return $this;
  }

  public function catersFor($iface)
  {
    $this->constructionSite->iface = $iface;
    $this->constructionSite->type = 'provider';
    return $this;
  }

  public function consumes($iface)
  {
    $this->constructionSite->iface = $iface;
    $this->constructionSite->type = 'consumer';
    return $this;
  }

  public function via($method)
  {
    $this->constructionSite->method = $method;

    $desc = new ServiceInterfaceDescription(
      $this->constructionSite->service,
      $this->constructionSite->iface,
      $this->constructionSite->method
    );

    switch ($this->constructionSite->type) {
      case 'consumer':
        $this->consumers[] = $desc;
        break;
      case 'provider':
        $this->providers[] = $desc;
        break;
      default:
        throw new DefensiveCodeException("Invalid type for service interface description.");
    }
  }
}