<?php

namespace Econemon\Bootsy\ErrorBundle\Service;

use Exception;

use Econemon\Bootsy\DatabaseBundle\Service\DatabaseService;
use Econemon\Bootsy\DatabaseBundle\Service\DatabaseServiceAware;
use Econemon\Bootsy\DatabaseBundle\Service\DatabaseUpdateProvider;
use Econemon\Bootsy\ErrorBundle\Model\ErrorHandlerException;
use Econemon\Bootsy\ErrorBundle\Model\LoggedException;
use Econemon\Bootsy\ErrorBundle\Model\LoggedExceptionMapper;

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

  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    $error = LoggedException::fromException($event->getException());

    try {
      $this->storeError($error);
      if ($this->raisesAlarm($error)) {
        $this->triggerNotifications($error);
      }
    } catch (Exception $internal) {
      $this->handleInternalException($event, $internal);
    }
  }

  /**
   * @param LoggedException $error
   * @throws \Econemon\Bootsy\ErrorBundle\Model\ErrorHandlerException
   */
  protected function storeError(LoggedException $error)
  {
    if (TRUE === $this->databaseService->verifyConnection($msg, $internalError)) {
      try {
        $id = $this->databaseService->selectOrInsert(LoggedExceptionMapper::getTableName(), LoggedExceptionMapper::arrayFromModel($error));
        $idOccurrence = $this->databaseService->insertOrSelect(LoggedExceptionMapper::getOccurenceTableName(), LoggedExceptionMapper::arrayOccurrence($id, $error));
      } catch (Exception $e) {
        throw new ErrorHandlerException('Error while writing error to the database', 0, $e);
      }
    } else {
      if (is_a($internalError, 'Exception')) {
        throw new ErrorHandlerException($msg, 0, $internalError);
      } else {
        throw new ErrorHandlerException($msg);
      }
    }
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
   * @param \Econemon\Bootsy\DatabaseBundle\Service\DatabaseService $databaseService
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

  /**
   * Bail out in some graceful way when the error handler itself has errors.
   * @param GetResponseForExceptionEvent $event
   * @param Exception $error
   */
  protected function handleInternalException(GetResponseForExceptionEvent $event, Exception $error)
  {
    // TODO
  }

  /**
   * @return LoggedException[]
   */
  public function loadErrors()
  {
    $loader = new LoggedExceptionMapper();
    return $this->databaseService->load($loader);
  }
}