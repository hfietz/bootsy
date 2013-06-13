<?php

namespace Econemon\Bootsy\ApplicationBundle\Exception;

use Exception;

class DefensiveCodeException extends ParametrizedMessageException
{
  const DEFAULT_BASE_MESSAGE = 'The following problem indicates a programming error: \'%message%\'';

  public function __construct($message = "", $code = 0, Exception $previous = null, $baseMessage = NULL)
  {
    parent::__construct($message, $code, $previous, $baseMessage);
  }

  public static function forUnexpectedTypeOf($offender, $expectedType)
  {
    $baseMessage = 'Expected \'%expectedType%\', got \'%typeDesc%\'';

    $error = new DefensiveCodeException("", 0, NULL, $baseMessage);

    $error->parameters = array();
    $error->setParameter('expectedType', $expectedType);
    $error->setParameter('typeDesc', self::describeTypeOf($offender));

    return $error;
  }

  public static function describeTypeOf($offender)
  {
    return is_object($offender) ? get_class($offender) : gettype($offender);
  }
}