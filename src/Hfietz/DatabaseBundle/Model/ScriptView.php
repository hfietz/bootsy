<?php

namespace Hfietz\DatabaseBundle\Model;

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
    $this->lastChanged = new DateTime('@' . $script->getCTime());
    $lastRun = $script->getLatestRun();
    if (NULL === $lastRun) {
      $this->status = 'new';
    } else {
      $this->lastTimeRun = new DateTime('@' . $lastRun->timestamp);
      $this->lastVersionRun = $lastRun->hash;
      if ($this->lastVersionRun === $this->currentVersion) {
        $this->status = 'current';
      } else {
        if ($lastRun->timestamp < $script->getCTime()) {
          $this->status = 'changed';
        } else {
          $this->status = 'outdated';
        }
      }
    }
  }
}