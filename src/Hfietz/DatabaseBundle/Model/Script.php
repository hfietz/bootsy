<?php

namespace Hfietz\DatabaseBundle\Model;

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

  /**
   * @param ScriptRun $run
   */
  public function addRun($run)
  {
    $this->runs[] = $run;
  }

  /**
   * @return ScriptRun|null
   */
  public function getLatestRun()
  {
    if (count($this->runs) === 0) {
      return NULL;
    }

    usort($this->runs, function ($runA, $runB) {
      /**
       * @var ScriptRun $runA
       * @var ScriptRun $runB
       */
      if ($runA->timestamp < $runB->timestamp) {
        return -1;
      } else if ($runA->timestamp > $runB->timestamp) {
        return 1;
      } else {
        return 0;
      }
    });

    return end($this->runs);
  }

  /**
   * @return string
   */
  public function getHash()
  {
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