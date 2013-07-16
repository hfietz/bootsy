<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

interface ObjectManagerInterface
{
  /**
   * @param array $criteria
   * @param array $orderBy
   * @return object|null
   */
  public function findOneBy(array $criteria, array $orderBy = NULL);

  /**
   * @param array $criteria
   * @param array $orderBy
   * @param null $limit
   * @param null $offset
   * @return array
   */
  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

  /**
   * @return object
   */
  public function newInstance();

  /**
   * @param object $object
   * @throws \Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException
   */
  public function save($object);

  /**
   * @return string
   */
  public function getClassName();
}