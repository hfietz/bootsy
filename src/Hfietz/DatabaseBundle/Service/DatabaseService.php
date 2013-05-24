<?php

namespace Hfietz\DatabaseBundle\Service;

use Exception;
use Hfietz\DatabaseBundle\Exception\DatabaseException;
use Hfietz\DatabaseBundle\Model\DatabaseConfiguration;
use Hfietz\DatabaseBundle\Model\Script;
use Hfietz\DatabaseBundle\Model\ScriptRun;
use Hfietz\DatabaseBundle\Service\Hydrator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class DatabaseService
{
  /**
   * relative to kernel root directory
   * @var string
   */
  public $configDir = 'config';

  /**
   * @var string
   */
  public $configFileName = 'parameters.yml';

  /**
   * @var string[]
   */
  protected $scriptPaths = array();

  /**
   * @var Connection
   */
  protected $db_connection;

  /**
   * @var KernelInterface
   */
  protected $kernel;

  /**
   * @return DatabaseConfiguration|null
   */
  public function loadConfigUsed()
  {
    return DatabaseConfiguration::fromConnection($this->db_connection);
  }

  public function verifyConnection(&$message = NULL, &$error = NULL)
  {
    $success = TRUE;

    try {
      if (NULL === $this->db_connection) {
        throw new DatabaseException('Missing a connection object, something went wrong with the dependency injection.');
      }

      if (FALSE === $this->db_connection->isConnected()) {
        $this->db_connection->connect();
        if (FALSE === $this->db_connection->isConnected()) {
          throw new DatabaseException('DB is not connected, but no error was thrown during connect.');
        }
      }
    } catch (Exception $e) {
      $success = FALSE;
      $message = $e->getMessage();
      $error = $e;
    }

    return $success;
  }

  /**
   * @param AbstractSchemaManager $schemaManager
   * @return Table
   */
  protected function getVersionsTable($schemaManager)
  {
    $versionsTable = 'meta.updateScripts';

    if (FALSE === $schemaManager->tablesExist(array($versionsTable))) {
      $table = $this->initVersionsTable($schemaManager, $versionsTable);
    } else {
      $table = $schemaManager->listTableDetails($versionsTable);
    }

    return $table;
  }

  /**
   * @param AbstractSchemaManager $schemaManager
   * @param string $tableName
   * @return Table
   */
  protected function initVersionsTable($schemaManager, $tableName)
  {
    $table = new Table($tableName);
    $table->addColumn('id', Type::INTEGER, array('autoincrement' => TRUE));
    $table->addColumn('file_path', Type::STRING);
    $table->addColumn('hash', Type::STRING);
    $table->addColumn('timestamp', Type::DATETIME);
    $table->setPrimaryKey(array('id'));
    try {
      $schemaManager->createTable($table);
    } catch (DBALException $e) {
      $cause = $e->getPrevious();
      if (is_a($cause, 'PDOException') && $cause->getCode() === '3F000') {
        $sql = 'CREATE SCHEMA ' . $table->getNamespaceName();
        $this->db_connection->exec($sql);
        $schemaManager->createTable($table);
      } else {
        throw $e;
      }
    }
    return $table;
  }

  public function getScriptPaths()
  {
    return $this->scriptPaths;
  }

  public function addScriptPath($path)
  {
    if (is_dir($this->getInstallationDir() . DIRECTORY_SEPARATOR . $path)) {
      $this->scriptPaths[] = $path;
    } else {
      throw new Exception("Could not find directory '" . $path . "'");
    }
  }

  protected function getInstallationDir()
  {
    return realpath($this->kernel->getRootDir() . DIRECTORY_SEPARATOR . '..');
  }

  /**
   * @param string|null $path
   * @return \Hfietz\DatabaseBundle\Model\Script[]
   */
  public function loadScripts($path = NULL)
  {
    /**
     * @var SplFileInfo $file
     * @var Script[] $scripts
     */

    $scripts = array();

    if (NULL === $path) {
      foreach ($this->getScriptPaths() as $path) {
        $scripts += $this->loadScripts($path);
      }

      foreach ($this->loadScriptRuns() as $run) {
        if (array_key_exists($run->filePath, $scripts)) {
          $scripts[$run->filePath]->addRun($run);
        }
      }
    } else {
      $finder = new Finder();
      $fs = new Filesystem();
      if (!$fs->isAbsolutePath($path)) {
        $path = realpath($this->getInstallationDir() . DIRECTORY_SEPARATOR . $path);
      }

      foreach ($finder->files()->in($path) as $file) {
        $scripts[$file->getPathname()] = Script::fromFileInfo($file);
      }
    }

    return $scripts;
  }

  /**
   * @return ScriptRun[]
   */
  public function loadScriptRuns()
  {
    $sm = $this->db_connection->getSchemaManager();
    $table = $this->getVersionsTable($sm);

    $qb = $this->db_connection->createQueryBuilder();
    $stmt = $qb->select('file_path', 'hash', 'timestamp')->from($table->getName(), 'v')->execute();
    $updatesRun = $stmt->fetchAll();

    array_walk($updatesRun, function (&$data) {
      $data = Hydrator::hydrate(new ScriptRun(), $data);
    });

    return $updatesRun;
  }

  /**
   * @return DatabaseConfiguration|null
   */
  public function loadConfig()
  {
    $yaml = $this->loadParsedYaml();

    $config = DatabaseConfiguration::fromParsedYaml($yaml);
    return $config;
  }

  /**
   * @param DatabaseConfiguration $config
   */
  public function saveConfig(DatabaseConfiguration $config)
  {
    $yaml = $this->loadParsedYaml();

    $parameters =& $yaml['parameters'];
    $parameters['database_driver'] = $config->driverName;
    $parameters['database_name'] = $config->databaseName;
    $parameters['database_host'] = $config->host;
    $parameters['database_user'] = $config->user;
    if (!empty($config->password)) {
      $parameters['database_password'] = $config->password;
    }

    $this->writeParsedYaml($yaml);
  }

  /**
   * @return string
   */
  protected function getConfigFilePath()
  {
    return $this->kernel->getRootDir() . DIRECTORY_SEPARATOR . $this->configDir . DIRECTORY_SEPARATOR . $this->configFileName;
  }

  /**
   * @return array
   */
  protected function loadParsedYaml()
  {
    $yaml = Yaml::parse(file_get_contents($this->getConfigFilePath()));
    return $yaml;
  }

  /**
   * @param array $yaml
   * @void
   */
  protected function writeParsedYaml($yaml)
  {
    $data = Yaml::dump($yaml);
    file_put_contents($this->getConfigFilePath(), $data);
  }

  /**
   * @param KernelInterface $kernel
   */
  public function setKernel($kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @param Connection $db_connection
   */
  public function setDbConnection($db_connection)
  {
    $this->db_connection = $db_connection;
  }

  /**
   * @return string
   */
  public function getConfigFileRelativePath()
  {
    $fs = new Filesystem();

    $fullPath = $this->getConfigFilePath();
    $dir = dirname($fullPath);
    $file = basename($fullPath);
    return $fs->makePathRelative($dir, $this->getInstallationRootDir()) . $file;
  }

  /**
   * @return string
   */
  public function getInstallationRootDir()
  {
    return realpath($this->kernel->getRootDir() . DIRECTORY_SEPARATOR . '..');
  }
}