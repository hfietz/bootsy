<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Service\DoctrineAware;
use Exception;
use Econemon\Bootsy\DatabaseBundle\Exception\DatabaseException;
use Econemon\Bootsy\DatabaseBundle\Model\DatabaseConfiguration;
use Econemon\Bootsy\DatabaseBundle\Model\Script;
use Econemon\Bootsy\DatabaseBundle\Model\ScriptRun;
use Econemon\Bootsy\DatabaseBundle\Service\Hydrator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

use PDO;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

class DatabaseService implements DoctrineAware
{
  const CLIENT_INTERFACE_NAME = 'Econemon\Bootsy\DatabaseBundle\Service\DatabaseServiceAware';
  const EXTENDER_INTERFACE_NAME = 'Econemon\Bootsy\DatabaseBundle\Service\DatabaseExtender';
  const SET_DATABASE_SERVICE = 'setDatabaseService';
  const SERVICE_ID = 'econemon_bootsy_database';
  const REGISTRATION_CALLBACK = 'registerSchemaProviderService';

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
   * @var RegistryInterface
   */
  public $doctrine;

  /**
   * @param DatabaseExtender $service
   */
  public function registerSchemaProviderService($service)
  {
    $path = $service->getDbScriptPath();
    if (NULL !== $path) {
      $this->scriptPaths[] = $path;
    }
  }

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
   * @return Table
   */
  protected function getVersionsTable()
  {
    /**
     * @var AbstractSchemaManager $schemaManager
     */
    $schemaManager = $this->db_connection->getSchemaManager();
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
      if ($this->isSchemaNotExists($e)) {
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

  protected function getInstallationDir()
  {
    return realpath($this->kernel->getRootDir() . '/..');
  }

  /**
   * @param string|null $path
   * @return \Econemon\Bootsy\DatabaseBundle\Model\Script[]
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
        $relPath = $this->makePathRelativeToInstallDir($file->getPathname());
        $scripts[$relPath] = Script::fromFileInfo($file, $relPath);
      }
    }

    return $scripts;
  }

  /**
   * @return ScriptRun[]
   */
  public function loadScriptRuns()
  {
    $table = $this->getVersionsTable();

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
    return $this->makePathRelativeToInstallDir($this->getConfigFilePath());
  }

  /**
   * @return string
   */
  public function getInstallationRootDir()
  {
    return realpath($this->kernel->getRootDir() . DIRECTORY_SEPARATOR . '..');
  }

  /**
   * @param Script $script
   */
  public function runScript(Script $script)
  {
    /**
     * @var PDO $driver
     */
    try {
      $driver = $this->db_connection->getWrappedConnection();
      if (is_a($driver, 'PDO')) {
        // Unfortunately, there is (seems to be) no way to query the current value of the attribute, so we can't set it
        // back when we're done.
        $driver->setAttribute(PDO::ATTR_EMULATE_PREPARES, TRUE);
      }
      $stmt = $this->db_connection->prepare($script->getContents());
      $stmt->execute();
      $this->logScriptRun($script);
    } catch (Exception $e) {
      throw $e; // TODO: Log script run with errors?
    }
  }

  /**
   * @param Script $script
   */
  protected function logScriptRun(Script $script)
  {
    $table = $this->getVersionsTable();
    $sql = 'INSERT INTO ' . $table->getName() . '(file_path, hash, timestamp) VALUES (?, ?, ?)';
    $stmt = $this->db_connection->prepare($sql);
    $stmt->bindValue(1, $this->makePathRelativeToInstallDir($script->getPathname()), PDO::PARAM_STR);
    $stmt->bindValue(2, $script->getHash(), PDO::PARAM_STR);
    $stmt->bindValue(3, $script->getDateTime()->format('c'), PDO::PARAM_STR);
    $stmt->execute();
  }

  /**
   * @param $fullPath
   * @return string
   */
  protected function makePathRelativeToInstallDir($fullPath)
  {
    $fs = new Filesystem();
    $dir = dirname($fullPath);
    $file = basename($fullPath);
    $relPath = $fs->makePathRelative($dir, $this->getInstallationRootDir()) . $file;
    return $relPath;
  }

  public function merge($tableName, $data, $keyFields = NULL, $idField = 'id')
  {
    $searchFields = $this->extractSearchFields($data, $keyFields);

    $id = $this->inTransaction(array($this, 'unsafeMerge'), $tableName, $data, $idField, $searchFields, TRUE);

    return $id;
  }

  public function selectOrInsert($tableName, $data, $keyFields = NULL, $idField = 'id')
  {
    $searchFields = $this->extractSearchFields($data, $keyFields);

    $id = $this->inTransaction(array($this, 'unsafeMerge'), $tableName, $data, $idField, $searchFields, FALSE);

    return $id;
  }

  public function startTransaction()
  {
    $this->db_connection->beginTransaction();
  }

  public function commitTransaction()
  {
    $this->db_connection->commit();
  }

  public function rollbackTransaction()
  {
    $this->db_connection->rollBack();
  }

  protected function unsafeMerge($tableName, $data, $idField, $searchFields, $withUpdate = TRUE)
  {
    $id = $this->unsafeSelectId($tableName, $searchFields, $idField);
    if (NULL === $id) {
      $success = $this->unsafeInsert($tableName, $data);
      if (TRUE === $success) {
        $id = $this->unsafeSelectId($tableName, $searchFields, $idField);
        return $id;
      } else {
        throw new DatabaseException('Insert failed for unknown reasons');
      }
    } else if (TRUE === $withUpdate) {
      $success = $this->unsafeUpdate($tableName, $data, $id, $idField);
    }
    return $id;
  }

  public function insertOrSelect($tableName, $data, $keyFields = NULL, $idField = 'id')
  {
    $searchFields = $this->extractSearchFields($data, $keyFields);

    try {
      $success = $this->unsafeInsert($tableName, $data);
      if (FALSE === $success) {
        throw new DatabaseException('Insert failed for unknown reasons');
      }
    } catch (Exception $e) {
      if (!$this->isDuplicateKeyViolation($e)) {
        throw $e;
      }
    }
    $id = $this->unsafeSelectId($tableName, $searchFields, $idField);

    return $id;
  }

  /**
   * @param $tableName
   * @param $data
   * @return bool
   */
  protected function unsafeInsert($tableName, $data)
  {
    $sql = 'INSERT INTO ' . $tableName . ' (' . join(', ', array_keys($data)) . ') VALUES(' . join(', ', array_fill(0, count($data), '?')) . ')';
    $stmt = $this->db_connection->prepare($sql);
    $ix = 1;
    foreach ($data as $value) {
      $stmt->bindValue($ix, $value);
      $ix += 1;
    }

    $success = $stmt->execute();
    return $success;
  }

  /**
   * @param $tableName
   * @param $fields
   * @param $searchFields
   * @return mixed
   */
  protected function unsafeSelect($tableName, $fields = NULL, $searchFields = array())
  {
    $select = $this->db_connection->createQueryBuilder()->select($fields);
    $select->from($tableName, 't');
    foreach ($searchFields as $name => $value) {
      $select->andWhere($name . ' = ?');
      $select->createPositionalParameter($value);
    }

    $sql = $select->getSQL();
    $result = $select->execute();
    return $result;
  }

  /**
   * @param $tableName
   * @param $searchFields
   * @param $idField
   * @return int|null
   */
  protected function unsafeSelectId($tableName, $searchFields = array(), $idField = 'id')
  {
    $result = $this->unsafeSelect($tableName, $idField, $searchFields);

    switch ($result->rowCount()) {
      case 0:
        $id = NULL;
        break;
      case 1:
        $id = (int)$result->fetchColumn();
        break;
      default:
        throw new DatabaseException('Ambigous search criteria delivered more than one result where exactly one was expected.');
    }

    return $id;
  }

  /**
   * @param $data
   * @param $keyFields
   * @return array
   */
  protected function extractSearchFields($data, $keyFields)
  {
    $searchFields = array();
    if (is_array($keyFields)) {
      foreach ($keyFields as $name) {
        if (array_key_exists($name, $data)) {
          $searchFields[$name] = $data[$name];
        }
      }
      return $searchFields;
    } else {
      $searchFields = $data;
      return $searchFields;
    }
  }

  /**
   * @param $fn
   * @return mixed
   * @throws \Exception
   */
  public function inTransaction($fn)
  {
    $this->startTransaction();
    try {
      $params = array_slice(func_get_args(), 1);
      $value = call_user_func_array($fn, $params);
      $this->commitTransaction();
    } catch (Exception $e) {
      $this->rollbackTransaction();
      throw $e;
    }
    return $value;
  }

  /**
   * @param Exception $e
   * @param $sqlError
   * @return bool
   */
  protected function isSqlError(Exception $e = NULL, $sqlError)
  {
    if (is_a($e, 'PDOException')) {
      return $e->getCode() === $sqlError;
    } else if (is_a($e, 'Doctrine\DBAL\DBALException')) {
      return $this->isSqlError($e->getPrevious(), $sqlError);
    } else {
      return FALSE;
    }
  }

  /**
   * @param Exception $e
   * @return bool
   */
  public function isSchemaNotExists(Exception $e = NULL)
  {
    return $this->isSqlError($e, '3F000');
  }

  /**
   * @param Exception $e
   * @return bool
   */
  public function isDuplicateKeyViolation(Exception $e = NULL)
  {
    return $this->isSqlError($e, '23505');
  }

  public function load(ObjectMapper $mapper, $keyColumn = 'id')
  {
    /**
     * @var Statement $stmt
     */
    $qb = $this->db_connection->createQueryBuilder();
    $mapper->buildSelectQuery($qb);
    $stmt = $qb->execute();

    $result = array();
    if ($stmt->rowCount() > 0) {
      foreach ($stmt->fetchAll() as $row) {
        $mapper->hydrate($row, $result, $keyColumn);
      }
    }

    return $result;
  }

  protected function unsafeUpdate($tableName, $data, $id, $idField = 'id')
  {
    if (array_key_exists($idField, $data)) {
      unset($data[$idField]);
    }

    $assignments = array();
    foreach (array_keys($data) as $name) {
      $assignments[] = sprintf('%1$s = :%1$s', $name);
    }

    $sql = 'UPDATE ' . $tableName . ' SET ' . join(', ', $assignments) . ' WHERE ' . $idField . ' = :' . $idField;
    $stmt = $this->db_connection->prepare($sql);

    foreach ($data as $name => $value) {
      $stmt->bindValue($name, $value);
    }
    $stmt->bindValue($idField, $id);

    $success = $stmt->execute();
    return $success;
  }

  public function setDoctrine(RegistryInterface $doctrine)
  {
    $this->doctrine = $doctrine;
  }

  /**
   * @return \Symfony\Bridge\Doctrine\RegistryInterface
   */
  public function getDoctrine()
  {
    return $this->doctrine;
  }
}