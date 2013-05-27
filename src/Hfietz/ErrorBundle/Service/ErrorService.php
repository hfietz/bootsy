<?php

namespace Hfietz\ErrorBundle\Service;

use Hfietz\DatabaseBundle\Service\DatabaseService;
use Hfietz\DatabaseBundle\Service\DatabaseServiceAware;
use Hfietz\ErrorBundle\Model\LoggedException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ErrorService implements EventSubscriberInterface, DatabaseServiceAware
{
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
}