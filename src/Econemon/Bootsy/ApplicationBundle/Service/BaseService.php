<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\DatabaseBundle\Service\DatabaseUpdateProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class BaseService implements DatabaseUpdateProvider
{
  /**
   * @var KernelInterface
   */
  protected $kernel;

  public function __construct(KernelInterface $kernel = NULL)
  {
    $this->kernel = $kernel;
  }

  /**
   * @return string
   */
  public function getDbScriptPath()
  {
    // TODO: We want a reliable and framework-compliant way to determine the bundle path relative to the installation root
    $fs = new Filesystem();
    $reflection = new \ReflectionObject($this);
    $path = realpath(dirname($reflection->getFileName()) . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, array('..', 'Resources', 'db')));
    $root = realpath($this->kernel->getRootDir() . '/..'); // KernelInterface::getRootDir always returns Unix-style paths

    return $path ? $fs->makePathRelative($path, $root) : NULL;
  }
}