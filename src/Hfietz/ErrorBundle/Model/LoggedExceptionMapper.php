<?php

namespace Hfietz\ErrorBundle\Model;

class LoggedExceptionMapper
{
  const SCHEMA_NAME = 'management';
  const TABLE_NAME = 'error';
  const TABLE_NAME_OCCURRENCE = 'error_occurrence';

  public static function arrayFromModel(LoggedException $source)
  {
    $breakpoint = 'here';
    return array(
      'file' => $source->getOriginalFile(),
      'line' => $source->getOriginalLine(),
      'message' => $source->getMessage(),
    );
  }

  public static function getTableName()
  {
    $dummy = 'set breakpoint here';
    return self::SCHEMA_NAME . '.' . self::TABLE_NAME;
  }

  public static function getOccurenceTableName()
  {
    return self::SCHEMA_NAME . '.' . self::TABLE_NAME_OCCURRENCE;
  }

  public static function arrayOccurrence($id, LoggedException $error)
  {
    return array(
      'error_id' => $id,
      'occurred_at' => $error->getIsoTimestamp(),
    );
  }
}