<?php

namespace Econemon\Bootsy\ApplicationBundle\Exception;

use Exception;

class ParametrizedMessageException extends Exception
{
  protected $parameters = array();

  const DEFAULT_BASE_MESSAGE = 'An error has occurred: \'%message%\'';

  public function __construct($message = "", $code = 0, Exception $previous = null, $baseMessage = NULL)
  {
    if (NULL === $baseMessage) {
      $baseMessage = static::DEFAULT_BASE_MESSAGE;
    }
    parent::__construct($baseMessage, $code, $previous);
    $this->setParameter('message', $message);
  }

  public function setParameter($name, $value)
  {
    $key = '%' . $name . '%';
    $this->parameters[$key] = $value;
  }

  /**
   * @return array
   */
  public function getParameters()
  {
    return $this->parameters;
  }
}