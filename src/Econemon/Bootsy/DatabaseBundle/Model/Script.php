<?php

namespace Econemon\Bootsy\DatabaseBundle\Model;

use DateTime;
use Exception;
use Symfony\Component\Finder\SplFileInfo;

class Script extends SplFileInfo
{
  /**
   * @var string
   */
  protected $hash;

  /**
   * @var ScriptRun[]
   */
  protected $runs = array();

  /**
   * @var bool
   */
  protected $areRunsSorted = FALSE;

  /**
   * Replaces a copy constructor which would end up smelly because the parent class already has different constructor params
   * @param SplFileInfo $fileInfo
   * @return Script
   */
  public static function fromFileInfo(SplFileInfo $fileInfo)
  {
    return new Script($fileInfo->getPathname(), $fileInfo->getRelativePath(), $fileInfo->getRelativePathname());
  }

  public function load()
  {
    if (FALSE === $this->isFile()) {
      throw new Exception("File '" . $this->getPathname() . "' not found.");
    }

    $this->hash = $this->calculateHash($this->getContents());
  }

  public function isNew()
  {
    return NULL === $this->getLatestRun();
  }

  public function isAtLatestVersion()
  {
    return !$this->isNew() && $this->getLatestRun()->hash === $this->getHash();
  }

  public function isOutdated()
  {
    return !$this->isAtLatestVersion() && $this->getLatestRun()->getDateTime()->getTimestamp() > $this->getCTime();
  }

  public function isUpdated()
  {
    return !$this->isAtLatestVersion() && $this->getLatestRun()->getDateTime()->getTimestamp() < $this->getCTime();
  }

  /**
   * @param ScriptRun $run
   */
  public function addRun($run)
  {
    $this->runs[] = $run;
    // TODO: It would be an optimization if, instead of just setting a dirty flag and resorting later, we would insert
    //       the new run in the right place of an already sorted set.
    $this->areRunsSorted = FALSE;
  }

  /**
   * @return ScriptRun|null
   */
  public function getLatestRun()
  {
    if (count($this->runs) === 0) {
      return NULL;
    }

    if (FALSE === $this->areRunsSorted) {
      usort($this->runs, function ($runA, $runB) {
        /**
         * @var ScriptRun $runA
         * @var ScriptRun $runB
         */
        if ($runA->getDateTime()->getTimestamp() < $runB->getDateTime()->getTimestamp()) {
          return -1;
        } else if ($runA->getDateTime()->getTimestamp() > $runB->getDateTime()->getTimestamp()) {
          return 1;
        } else {
          return 0;
        }
      });

      $this->areRunsSorted = TRUE;
    }

    return end($this->runs);
  }

  public function getDateTime()
  {
    return new DateTime('@' . $this->getCTime());
  }

  /**
   * @return string
   */
  public function getHash()
  {
    if (NULL === $this->hash && TRUE === $this->isFile()) {
      $this->hash = $this->calculateHash($this->getContents());
    }

    return $this->hash;
  }

  /**
   * @param string $content
   * @return string
   */
  public function calculateHash($content)
  {
    // We assume that we are using the hashes here in a security-insensitive manner, and thus prefer md5 for its efficiency.
    return md5($content);
  }
}