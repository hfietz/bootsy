<?php

namespace Econemon\Bootsy\ErrorBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Econemon\Bootsy\DatabaseBundle\Service\ObjectMapper;

class LoggedExceptionMapper implements ObjectMapper
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

  /**
   * builds a select query directly on the QueryBuilder param, so the caller can use execute()
   * on that without the need for this method to return anything.
   *
   * @param QueryBuilder $builder
   * @void
   */
  public function buildSelectQuery(QueryBuilder $builder)
  {
    $builder->select('e.id', 'e.file', 'e.line', 'e.message', 'o.occurred_at')
      ->from(self::getTableName(), 'e')
      ->leftJoin('e', self::getOccurenceTableName(), 'o', 'e.id = o.error_id')
      ->orderBy('o.occurred_at', 'DESC');
  }

  /**
   * @param array $data one row from the result set
   * @param array $result
   * @param string $keyColumn
   * @void
   */
  public function hydrate($data, array &$result = array(), $keyColumn = 'id')
  {
    /**
     * @var LoggedException[] $result
     */
    $id = $data[$keyColumn];
    if (!array_key_exists($id, $result)) {
      $result[$id] = LoggedException::fromData($data);
    } else {
      $result[$id]->occurredAt(LoggedException::floatSecFromIso($data['occurred_at']));
    }
  }
}