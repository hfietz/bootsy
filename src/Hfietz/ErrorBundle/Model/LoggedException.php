<?php

namespace Hfietz\ErrorBundle\Model;

use DateTime;
use Exception;

class LoggedException extends Exception
{
  /**
   * @var int|null
   */
  protected $occurrences = array();

  /**
   * @var float|null
   */
  protected $timestamp = NULL;

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
   * @param $usec
   * @return string
   */
  public static function isoFromFloatSec($usec)
  {
    return strftime('%FT%T', self::secFromFloatSec($usec)) . sprintf('.%d', (int)(fmod($usec, 10 ^ 6) * 10 ^ 6)) . 'Z';
  }

  /**
   * @param $usec
   * @return int
   */
  public static function secFromFloatSec($usec)
  {
    return (int)$usec;
  }

  /**
   * @return int
   */
  public function getNumberOfOccurrences()
  {
    return count($this->occurrences);
  }

  public function getErrorClass()
  {
    return NULL === $this->getPrevious() ? get_class($this) : get_class($this->getPrevious());
  }

  public function getOriginalFile()
  {
    return NULL === $this->getPrevious() ? $this->getFile() : $this->getPrevious()->getFile();
  }

  public function getOriginalLine()
  {
    return NULL === $this->getPrevious() ? $this->getLine() : $this->getPrevious()->getLine();
  }

  /**
   * @return float|null
   */
  public function getMilliseconds()
  {
    return NULL === $this->timestamp ? NULL : $this->timestamp * 1000.0;
  }

  /**
   * @return float|null
   */
  public function getMicrotime()
  {
    return $this->timestamp;
  }

  /**
   * @return int
   */
  public function getUnixtime()
  {
    return self::secFromFloatSec($this->timestamp);
  }

  /**
   * @return DateTime
   */
  public function getDateTime()
  {
    return new DateTime($this->getIsoTimestamp());
  }

  /**
   * @return string
   */
  public function getIsoTimestamp()
  {
    return self::isoFromFloatSec($this->timestamp);
  }
}