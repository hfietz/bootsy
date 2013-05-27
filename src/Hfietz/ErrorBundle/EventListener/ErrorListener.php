<?php

namespace Hfietz\ErrorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Hfietz\ErrorBundle\Model\LoggedException;

class ErrorListener implements EventSubscriberInterface
{
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
}