<?php

namespace Econemon\Bootsy\ApplicationBundle\Exception;

use Exception;

class DefensiveCodeException extends ParametrizedMessageException
{
  const BASE_MSG_DEFAULT = 'The following problem indicates a programming error: \'%message%\'';

  const BASE_MSG_UNEXPECTED_TYPE = 'Expected \'%expectedType%\', got \'%typeDesc%\'';

  const BASE_MSG_UNEXPECTED_VALUE = 'Unexpected value \'%value%\' for \'%identifier%\', expected \'%expected%\'';

  public function __construct($message = "", $code = 0, Exception $previous = null, $baseMessage = NULL)
  {
    parent::__construct($message, $code, $previous, $baseMessage);
  }

  public static function forUnexpectedTypeOf($offender, $expectedType)
  {
    $error = self::fromBaseMessage(self::BASE_MSG_UNEXPECTED_TYPE);

    $error->parameters = array();
    $error->setParameter('expectedType', $expectedType);
    $error->setParameter('typeDesc', self::describeTypeOf($offender));

    return $error;
  }

  public static function describeTypeOf($offender)
  {
    return is_object($offender) ? get_class($offender) : gettype($offender);
  }

  /**
   * @param string $identifier
   * @param mixed $value
   * @param mixed $expected
   * @return DefensiveCodeException
   */
  public static function forUnexpectedValue($identifier, $value, $expected)
  {
    $error = self::fromBaseMessage(self::BASE_MSG_UNEXPECTED_VALUE);

    if (is_array($expected)) {
      $expected = 'one of ' . join(', ', $expected);
    }

    if (!is_scalar($value)) {
      $value = self::describeTypeOf($value);
    }

    $error->setParameter('identifier', $identifier);
    $error->setParameter('value', $value);
    $error->setParameter('expected', $expected);

    return $error;
  }

  /**
   * @param $baseMessage
   * @return DefensiveCodeException
   */
  public static function fromBaseMessage($baseMessage, $params = array())
  {
    $error = new DefensiveCodeException("", 0, NULL, $baseMessage);

    foreach ($params as $name => $value) {
      $error->setParameter($name, $value);
    }

    return $error;
  }
}