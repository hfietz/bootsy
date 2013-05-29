<?php

namespace Hfietz\DatabaseBundle\Service;

interface DatabaseUpdateProvider
{
  /**
   * @return string
   */
  public function getDbScriptPath();
}