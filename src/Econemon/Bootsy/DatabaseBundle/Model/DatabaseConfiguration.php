<?php

namespace Econemon\Bootsy\DatabaseBundle\Model;

use Doctrine\DBAL\Connection;
use Exception;

class DatabaseConfiguration
{
  /**
   * @var string|NULL
   */
  public $driverName;

  /**
   * @var string|NULL
   */
  public $databaseName;

  /**
   * @var string|NULL
   */
  public $host;

  /**
   * @var string|NULL
   */
  public $user;

  /**
   * @var string|NULL
   */
  public $password;

  /**
   * @return array
   */
  public function getView()
  {
    return array(
      'database driver' => $this->driverName,
      'database name' => $this->databaseName,
      'database host' => $this->host,
      'database user' => $this->user,
      'database password' => empty($this->password) ? 'empty' : 'not disclosed',
    );
  }

  /**
   * @param array $yaml
   * @return DatabaseConfiguration|null
   */
  public static function fromParsedYaml($yaml, $key = 'parameters')
  {
    if (NULL === $yaml) {
      return NULL;
    }

    if (!is_array($yaml) || !array_key_exists($key, $yaml) || !is_array($yaml[$key])) {
      throw new Exception('Unexpected type or structure for data source.');
    }

    $parameters = $yaml[$key];

    $config = new DatabaseConfiguration();
    $config->driverName = $parameters['database_driver'];
    $config->databaseName = $parameters['database_name'];
    $config->host = $parameters['database_host'];
    $config->user = $parameters['database_user'];
    $config->password = $parameters['database_password'];

    return $config;
  }

  /**
   * @param Connection $connection
   * @return DatabaseConfiguration|null
   */
  public static function fromConnection(Connection $connection = NULL)
  {
    if (NULL === $connection) {
      return NULL;
    }

    $config = new DatabaseConfiguration();

    $config->driverName = $connection->getDriver()->getName();
    $config->host = $connection->getHost();
    $config->databaseName = $connection->getDatabase();
    $config->user = $connection->getUsername();
    $config->password = $connection->getPassword();

    return $config;
  }
}