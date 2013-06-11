<?php

namespace Econemon\Bootsy\ApplicationBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Econemon\Bootsy\DatabaseBundle\Service\Hydrator;
use Econemon\Bootsy\DatabaseBundle\Service\ObjectMapper;
use PDO;

class ConfigItemMapper implements ObjectMapper
{
  protected $configItemName = NULL;

  const UNIQUE_COLUMN = 'machine_name';

  const TABLE_NAME = 'config_item';

  public function __construct($name = NULL)
  {
    $this->configItemName = $name;
  }

  /**
   * @return string
   */
  public static function getTableName()
  {
    return self::TABLE_NAME;
  }

  /**
   * @param ConfigItem $item
   * @return array
   */
  public static function export(ConfigItem $item)
  {
    return Hydrator::export($item, self::getColumnToPropertyMapping());
  }

  /**
   * @return array
   */
  public static function getColumnToPropertyMapping()
  {
    $mapping = array(
      'display_text' => 'displayText',
      'machine_name' => 'machineName',
      'value' => 'value',
    );
    return $mapping;
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
    $builder->select('id', self::UNIQUE_COLUMN, 'display_text', 'value')
      ->from(self::TABLE_NAME, 't')
      ->orderBy('display_text');

    if (NULL !== $this->configItemName) {
      $builder->andWhere('machine_name = ?');
      $builder->createPositionalParameter($this->configItemName, PDO::PARAM_STR);
    }
  }

  /**
   * @param array $data one row from the result set
   * @param array $result
   * @param string $keyColumn
   * @void
   */
  public function hydrate($data, array &$result = array(), $keyColumn = 'id')
  {
    $id = $data[$keyColumn];
    $item = new ConfigItem();
    Hydrator::hydrate($item, $data, self::getColumnToPropertyMapping());
    $result[$id] = $item;
  }

  /**
   * @return string
   */
  public static function getUniqueFieldName()
  {
    return self::UNIQUE_COLUMN;
  }
}