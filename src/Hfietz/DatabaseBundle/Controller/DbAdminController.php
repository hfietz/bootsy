<?php
namespace Hfietz\DatabaseBundle\Controller;

use Exception;

use Hfietz\DatabaseBundle\Form\Model\ConfigFormData;
use Hfietz\DatabaseBundle\Form\Type\ConfigForm;
use Hfietz\DatabaseBundle\Model\DatabaseConfiguration;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
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
    if ($this->db_connection->isConnected()) {
      return new Response('<h1>Hi, this is the DB versions page, we\'re not quite ready yet</h1>');
    } else {
      return $this->statusAction(); // TODO: Investigate: How will forwarding be handled in 2.3? Are there any issues forwarding like this?
    }
  }

  public function statusAction()
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

    return $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
  }

  public function configureAction(Request $req)
  {
    $formData = new ConfigFormData();

    $form = $this->formFactory->create(new ConfigForm(), $formData);

    if ($req->isMethod('POST')) {
      $form->bind($req);

      // TODO: validation
      if ($form->isValid()) {
        $config = $formData->toConfig();
        $this->saveConfig($config);
      } else {
        // TODO
      }
    }

    return new RedirectResponse($this->router->generate('db_status'));
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
        $this->db_connection->connect(); // This is likely to throw an exception, otherwise we would probably be connected
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
    $parameters['database_password'] = $config->password;

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
}