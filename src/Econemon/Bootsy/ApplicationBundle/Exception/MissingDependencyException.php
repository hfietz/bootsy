<?php

namespace Econemon\Bootsy\ApplicationBundle\Exception;

class MissingDependencyException extends \Exception
{
  public static function createFor($caller, $requiredDependency)
  {
    $message = sprintf('A %s object is missing the dependency \'%s\'', get_class($caller), $requiredDependency);
    return new MissingDependencyException($message);
  }
}