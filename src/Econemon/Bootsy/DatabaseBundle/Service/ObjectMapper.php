<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

use Doctrine\DBAL\Query\QueryBuilder;

interface ObjectMapper
{
  /**
   * builds a select query directly on the QueryBuilder param, so the caller can use execute()
   * on that without the need for this method to return anything.
   *
   * @param QueryBuilder $builder
   * @void
   */
  public function buildSelectQuery(QueryBuilder $builder);

  /**
   * @param array $data one row from the result set
   * @param array $result
   * @param string $keyColumn
   * @void
   */
  public function hydrate($data, array &$result = array(), $keyColumn = 'id');
}