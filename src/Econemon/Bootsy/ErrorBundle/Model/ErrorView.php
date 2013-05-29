<?php

namespace Econemon\Bootsy\ErrorBundle\Model;

class ErrorView
{
  public $message;
  public $file;
  public $line;
  public $count;
  public $timestamp;

  public static function fromLoggedException(LoggedException $error)
  {
    $view = new ErrorView();

    $view->message = $error->getMessage();
    $view->file = $error->getOriginalFile();
    $view->line = $error->getOriginalLine();
    $view->count = $error->getNumberOfOccurrences();
    $view->timestamp = $error->getIsoTimestamp();

    return $view;
  }
}