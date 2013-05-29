<?php

namespace Econemon\Bootsy\ErrorBundle\Model;

use DateTime;
use DateTimeZone;
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

    $this->occurredAt(microtime(TRUE));
  }

  public static function fromException(Exception $e)
  {
    return new LoggedException($e->getMessage(), $e->getCode(), $e);
  }

  public static function fromData($data)
  {
    $ex = new LoggedException($data['message']);
    $ex->file = $data['file'];
    $ex->line = $data['line'];
    $ex->timestamp = self::floatSecFromIso($data['occurred_at']);
    $ex->occurrences = array($ex->timestamp);

    return $ex;
  }

  /**
   * @param $isoString
   * @return float
   */
  public static function floatSecFromIso($isoString)
  {
    $dt = new DateTime($isoString, new DateTimeZone('UTC'));
    $ts = floatval($dt->format('U.u'));
    return $ts;
  }

  /**
   * @param $usec
   * @return string
   */
  public static function isoFromFloatSec($usec)
  {
    $frac = (string)$usec;
    $frac = substr($frac, strpos($frac, '.'));
    return gmstrftime('%FT%T', self::secFromFloatSec($usec)) . $frac . 'Z';
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

  /**
   * @param float $microtime
   */
  public function occurredAt($microtime)
  {
    $microtime = floatval($microtime); // just to be sure
    $position = $this->searchOccurrence($microtime);
    array_splice($this->occurrences, $position, 0, array($microtime));
    $this->timestamp = count($this->occurrences) > 0 ? end($this->occurrences) : NULL;
  }

  /**
   * @param float $microtime
   */
  protected function searchOccurrence($microtime, $start = NULL, $end = NULL)
  {
    if (NULL === $start) {
      $start = 0;
      $end = count($this->occurrences) - 1;
    }

    if ($end < $start) {
      return $start;
    }

    $position = $start + (int) floor(($end - $start) / 2);

    if ($start == $end || !array_key_exists($position, $this->occurrences)) {
      return $position;
    } else {
      if ($this->occurrences[$position] > $microtime) {
        return $this->searchOccurrence($microtime, $start, $position - 1);
      } else {
        return $this->searchOccurrence($microtime, $position + 1, $end);
      }
    }
  }
}