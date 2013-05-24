<?php
namespace Hfietz\DatabaseBundle\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Exception;

use Hfietz\DatabaseBundle\Form\Model\ConfigFormData;
use Hfietz\DatabaseBundle\Form\Type\ConfigForm;
use Hfietz\DatabaseBundle\Model\DatabaseConfiguration;
use Hfietz\DatabaseBundle\Model\Script;
use Hfietz\DatabaseBundle\Model\ScriptRun;
use Hfietz\DatabaseBundle\Model\ScriptView;
use Hfietz\DatabaseBundle\Service\Hydrator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

use Doctrine\DBAL\Connection;

use Hfietz\DatabaseBundle\Exception\DatabaseException;

// TODO: secure those methods / routes
class DbAdminController
{
  protected $scriptPaths = array();

  protected $parametersFilePath = 'config/parameters.yml';
  /**
   * @var Connection
   */
  protected $db_connection;

  /**
   * @var EngineInterface
   */
  protected $template_engine;

  /**
   * @var KernelInterface
   */
  protected $kernel;

  /**
   * @var Router
   */
  protected $router;

  /**
   * @var FormFactoryInterface
   */
  protected $formFactory;

  public function versionsAction()
  {
    /**
     * @var Statement $stmt
     * @var ScriptRun[] $updatesRun
     * @var Script[] $updatesAvailable
     */
    if (FALSE === $this->verifyConnection()) {
      return $this->statusAction(); // TODO: Investigate: How will forwarding be handled in 2.3? Are there any issues forwarding like this?
    } else {
      $sm = $this->db_connection->getSchemaManager();
      $table = $this->getVersionsTable($sm);
      $qb = $this->db_connection->createQueryBuilder();
      $stmt = $qb->select('file_path', 'hash', 'timestamp')->from($table->getName(), 'v')->execute();
      $updatesRun = $stmt->fetchAll();
      array_walk($updatesRun, function (&$data) {
        $data = Hydrator::hydrate(new ScriptRun(), $data);
      });

      $updatesAvailable = array();
      foreach ($this->getScriptPaths() as $path) {
        $updatesAvailable += $this->loadScripts($path);
      }

      foreach ($updatesRun as $run) {
        if (array_key_exists($run->filePath, $updatesAvailable)) {
          $updatesAvailable[$run->filePath]->addRun($run);
        }
      }

      $versions = array();
      foreach ($updatesAvailable as $script) {
        $versions[] = new ScriptView($script);
      }

      $variables = array(
        'versions' => $versions,
      );
      return $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_versions.html.twig', $variables);
    }
  }

  public function statusAction()
  {
    $variables = $this->getVariablesForStatusReport();

    return $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
  }

  public function configureAction(Request $req)
  {
    $response = new RedirectResponse($this->router->generate('db_status'));

    $formData = new ConfigFormData();

    $form = $this->formFactory->create(new ConfigForm(), $formData);

    if ($req->isMethod('POST')) {
      $form->bind($req);

      // TODO: validation
      if ($form->isValid()) {
        $config = $formData->toConfig();
        $this->saveConfig($config);
      } else {
        $variables = $this->getVariablesForStatusReport();

        $variables['config']['form'] = $form->createView();

        $response = $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
      }
    }

    return $response;
  }

  /**
   * @param Connection $db_connection
   */
  public function setDbConnection($db_connection)
  {
    $this->db_connection = $db_connection;
  }

  /**
   * @param mixed $template_engine
   */
  public function setTemplateEngine($template_engine)
  {
    $this->template_engine = $template_engine;
  }

  /**
   * @throws \Exception
   * @return \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
   */
  public function getTemplateEngine()
  {
    if (NULL === $this->template_engine) {
      throw new Exception('Dependency injection failed: No template engine available.');
    }
    return $this->template_engine;
  }

  protected function verifyConnection(&$message = NULL, &$error = NULL)
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

  protected function getConfigReport()
  {
    $config = $this->loadConfig();

    $report = array(
      'file' => $this->parametersFilePath,
      'parameters' => $config,
    );

    $formData = ConfigFormData::fromConfig($config);

    $options = array();

    $form = $this->formFactory->create(new ConfigForm(), $formData, $options);

    $report['form'] = $form->createView();

    return $report;
  }

  /**
   * @param KernelInterface $kernel
   */
  public function setKernel($kernel)
  {
    $this->kernel = $kernel;
  }

  /**
   * @param mixed $router
   */
  public function setRouter($router)
  {
    $this->router = $router;
  }

  /**
   * @param FormFactoryInterface $formFactory
   */
  public function setFormFactory($formFactory)
  {
    $this->formFactory = $formFactory;
  }

  /**
   * @return DatabaseConfiguration|null
   */
  protected function loadConfig()
  {
    $yaml = $this->loadParsedYaml();

    $config = DatabaseConfiguration::fromParsedYaml($yaml);
    return $config;
  }

  /**
   * @param DatabaseConfiguration $config
   */
  protected function saveConfig(DatabaseConfiguration $config)
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
    return $this->kernel->getRootDir() . '/' . $this->parametersFilePath;
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
   * @return array
   */
  protected function getVariablesForStatusReport()
  {
    $variables = array(
      'message' => 'Checking DB status...',
      'messageType' => 'message',
      'dbStatus' => 'unknown',
    );

    if (FALSE === $this->verifyConnection($variables['message'])) {
      $variables['messageType'] = 'error';
      $variables['dbStatus'] = 'no connection';
    } else {
      $variables['message'] = 'DB connection is up';
      $variables['messageType'] = 'success';
      $variables['dbStatus'] = 'connected';
    }

    $variables['parameters'] = DatabaseConfiguration::fromConnection($this->db_connection);

    $variables['config'] = $this->getConfigReport();
    return $variables;
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

  protected function loadScripts($path)
  {
    /**
     * @var SplFileInfo $file
     */
    $finder = new Finder();
    $fs = new Filesystem();
    if (!$fs->isAbsolutePath($path)) {
      $path = realpath($this->getInstallationDir() . DIRECTORY_SEPARATOR . $path);
    }

    $scripts = array();
    foreach ($finder->files()->in($path) as $file) {
      $scripts[$file->getPathname()] = Script::fromFileInfo($file);
    }

    return $scripts;
  }
}