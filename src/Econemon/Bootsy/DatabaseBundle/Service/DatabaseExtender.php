<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

interface DatabaseExtender
{
  /**
   * @return string
   */
  public function getDbScriptPath();
}