<?php

namespace Hfietz\ErrorBundle\Model;

use Exception;

class LoggedException extends Exception
{
  /**
   * @var int|null
   */
  protected $numberOfOccurrences = NULL;

  /**
   * @var float|null
   */
  protected $timestamp = NULL;

  /**
   * @var int|null
   */
  protected $id = NULL;

  public function __construct($message = "", $code = 0, Exception $previous = null)
  {
    parent::__construct($message, 0, $previous);

    // Because some Exception subclasses have string codes (e. g. SQL error codes),
    // and we can't pass those to the Exception constructor. Sheesh.
    $this->code = $code;

    $this->timestamp = microtime(TRUE);
  }

  public static function fromException(Exception $e)
  {
    return new LoggedException($e->getMessage(), $e->getCode(), $e);
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param int $id
   * @param bool $forceOverride
   * @return $this
   */
  public function setId($id, $forceOverride = FALSE)
  {
    if (NULL === $this->id || $forceOverride === TRUE) {
      $this->id = $id;
    }

    return $this;
  }

  /**
   * @return int|null
   */
  public function getNumberOfOccurrences()
  {
    return $this->numberOfOccurrences;
  }

  /**
   * @param int|null $occurrences
   * @param bool $forceOverride
   * @return $this
   */
  public function setNumberOfOccurrences($occurrences, $forceOverride = FALSE)
  {
    if (NULL === $this->numberOfOccurrences || TRUE === $forceOverride) {
      $this->numberOfOccurrences = $occurrences;
    }

    return $this;
  }

  /**
   * @return float|null
   */
  public function getMilliseconds()
  {
    return NULL === $this->timestamp ? NULL : $this->timestamp * 1000.0;
  }

  /**
   * @param float $timestamp
   * @param bool $forceOverride
   * @return $this
   */
  public function setMilliseconds($timestamp, $forceOverride = FALSE)
  {
    if (NULL === $this->timestamp || TRUE === $forceOverride) {
      $this->timestamp = $timestamp / 1000.0;
    }

    return $this;
  }

  /**
   * @return float|null
   */
  public function getTimestamp()
  {
    return $this->timestamp;
  }
}