<?php

namespace Hfietz\DatabaseBundle\Service;

interface DatabaseServiceAware
{
  /**
   * @param \Hfietz\DatabaseBundle\Service\DatabaseService $databaseService
   */
  function setDatabaseService(DatabaseService $databaseService = NULL);
}