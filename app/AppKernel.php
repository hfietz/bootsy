<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
  /**
   * Returns an array of bundles to registers.
   *
   * @return BundleInterface[] An array of bundle instances.
   *
   * @api
   */
  public function registerBundles()
  {
    $bundles = array(
      new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
      new Symfony\Bundle\SecurityBundle\SecurityBundle(),
      new Symfony\Bundle\TwigBundle\TwigBundle(),
    );

    if (in_array($this->getEnvironment(), array('dev', 'test'))) {
      $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
    }

    return $bundles;
  }

  /**
   * Loads the container configuration
   *
   * @param LoaderInterface $loader A LoaderInterface instance
   *
   * @api
   */
  public function registerContainerConfiguration(LoaderInterface $loader)
  {
    $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
  }
}