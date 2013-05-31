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

  public $preselect = FALSE;

  public function __construct(Script $script)
  {
    $this->file = $script->getNormalizedPath();
    $this->currentVersion = $this->shortenVersionForDisplay($script->getHash());
    $this->lastChanged = $script->getDateTime();
    if ($script->isNew()) {
      $this->status = 'new';
    } else {
      $lastRun = $script->getLatestRun();
      $this->lastTimeRun = $lastRun->getDateTime();
      $this->lastVersionRun = $this->shortenVersionForDisplay($lastRun->hash);
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

    $this->preselect = $script->isNew() || $script->isUpdated();
  }

  public function shortenVersionForDisplay($hash)
  {
    if (strlen($hash) < 8) {
      return $hash;
    } else {
      return substr($hash, 0, 4) . '..' . substr($hash, -4);
    }
  }
}