<?php

namespace Hfietz\ErrorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

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
    $f = $e;

    // TODO
  }
}