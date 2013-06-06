<?php

namespace Econemon\Bootsy\ApplicationBundle\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Econemon\Bootsy\DatabaseBundle\Service\ObjectMapper;
use PDO;

class ConfigItemMapper implements ObjectMapper
{
  protected $configItemName = NULL;

  public function __construct($name = NULL)
  {
    $this->configItemName = $name;
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
    $builder->select('id', 'machine_name', 'display_text', 'value')
      ->from('config_item', 't')
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
    $result[$id] = ConfigItem::fromData($data);
  }
}