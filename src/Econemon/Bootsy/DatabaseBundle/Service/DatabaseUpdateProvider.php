<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

interface DatabaseUpdateProvider
{
  /**
   * @return string
   */
  public function getDbScriptPath();
}