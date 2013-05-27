<?php

namespace Hfietz\DatabaseBundle\Model;

use DateTime;

class ScriptRun
{
  public $filePath;

  public $hash;

  public $timestamp;

  public function getDateTime()
  {
    if (is_string($this->timestamp)) {
      return new DateTime($this->timestamp);
    } else {
      return NULL;
    }
  }
}