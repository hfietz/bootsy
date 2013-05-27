<?php

namespace Hfietz\ErrorBundle\Service;

use Hfietz\DatabaseBundle\Service\DatabaseService;
use Hfietz\DatabaseBundle\Service\DatabaseServiceAware;
use Hfietz\DatabaseBundle\Service\DatabaseUpdateProvider;
use Hfietz\ErrorBundle\Model\LoggedException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class ErrorService implements EventSubscriberInterface, DatabaseServiceAware, DatabaseUpdateProvider
{
  /**
   * @var KernelInterface
   */
  protected $kernel;

  /**
   * @var DatabaseService
   */
  protected $databaseService;

  public static function getSubscribedEvents()
  {
    return array(
      'kernel.exception' => array('onKernelException', 0),
    );
  }

  public function __construct(KernelInterface $kernel = NULL)
  {
    $this->kernel = $kernel;
  }

  public function onKernelException(GetResponseForExceptionEvent $e)
  {
    $error = LoggedException::fromException($e->getException());

    $this->storeError($error);
    if ($this->raisesAlarm($error)) {
      $this->triggerNotifications($error);
    }
  }

  protected function storeError($error)
  {
    // TODO
  }

  protected function raisesAlarm($error)
  {
    // TODO
    return TRUE;
  }

  protected function triggerNotifications($error)
  {
    // TODO
  }

  /**
   * @param \Hfietz\DatabaseBundle\Service\DatabaseService $databaseService
   */
  function setDatabaseService(DatabaseService $databaseService = NULL)
  {
    $this->databaseService = $databaseService;
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

    return $fs->makePathRelative($path, $root);
  }
}