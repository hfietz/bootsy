<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Exception\MissingDependencyException;
use Econemon\Bootsy\DatabaseBundle\Service\DatabaseService;
use Econemon\Bootsy\DatabaseBundle\Service\DatabaseServiceAware;
use Symfony\Component\HttpKernel\KernelInterface;

class RegistryService implements DatabaseServiceAware
{
  /**
   * @var KernelInterface
   */
  protected $kernel;
  /**
   * @var DatabaseService
   */
  protected $databaseService;

  /**
   * @var string
   */
  protected $installationRoot;

  /**
   * @param KernelInterface $kernel
   */

  public function __construct(KernelInterface $kernel = NULL)
  {
    $this->kernel = $kernel;

    // KernelInterface::getRootDir always returns Unix-style paths
    $this->installationRoot = realpath($this->kernel->getRootDir() . '/..');
  }

  /**
   * @param DatabaseService $databaseService
   */
  function setDatabaseService(DatabaseService $databaseService = NULL)
  {
    $this->databaseService = $databaseService;
  }

  /**
   * @return DatabaseService
   */
  public function getDatabaseService()
  {
    if (NULL === $this->databaseService) {
      throw MissingDependencyException::createFor($this, 'DatabaseService');
    }

    return $this->databaseService;
  }

  /**
   * @return string
   */
  public function getWebPath()
  {
    return $this->installationRoot . '/web';
  }

  /**
   * @return string
   */
  public function getImagePath()
  {
    return $this->installationRoot . '/web/img';
  }

  /**
   * @return string
   */
  public function getInstallationRoot()
  {
    return $this->installationRoot;
  }
}