<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

interface DatabaseServiceAware
{
  /**
   * @param \Econemon\Bootsy\DatabaseBundle\Service\DatabaseService $databaseService
   */
  function setDatabaseService(DatabaseService $databaseService = NULL);
}