<?php

namespace Econemon\Bootsy\DatabaseBundle\Model;

use DateTime;

class ScriptView
{
  public $file;

  public $currentVersion;

  public $lastChanged;

  public $lastVersionRun;

  public $lastTimeRun;

  public $status;

  public function __construct(Script $script)
  {
    $this->file = $script->getRelativePathname();
    $this->currentVersion = $script->getHash();
    $this->lastChanged = $script->getDateTime();
    if ($script->isNew()) {
      $this->status = 'new';
    } else {
      $lastRun = $script->getLatestRun();
      $this->lastTimeRun = $lastRun->getDateTime();
      $this->lastVersionRun = $lastRun->hash;
      if ($script->isAtLatestVersion()) {
        $this->status = 'current';
      } else {
        if ($script->isOutdated()) {
          $this->status = 'outdated';
        } else {
          $this->status = 'changed';
        }
      }
    }
  }
}