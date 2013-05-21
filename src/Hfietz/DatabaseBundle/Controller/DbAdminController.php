<?php
namespace Hfietz\DatabaseBundle\Controller;

use Exception;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Doctrine\DBAL\Connection;

use Hfietz\DatabaseBundle\Exception\DatabaseException;

class DbAdminController
{
  /**
   * @var Connection
   */
  protected $db_connection;

  /**
   * @var EngineInterface
   */
  protected $template_engine;

  /**
   * @var HttpKernelInterface
   */
  protected $http_kernel;

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

    $variables['parameters'] = $this->get_database_params_for_status_report();

    return $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
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
   * @param HttpKernelInterface $http_kernel
   */
  public function setHttpKernel($http_kernel)
  {
    $this->http_kernel = $http_kernel;
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

  /**
   * @param $parameters
   * @return mixed
   */
  protected function get_database_params_for_status_report()
  {
    if (NULL === $this->db_connection) {
      return NULL;
    }

    $parameters = array(
      'database driver' => $this->db_connection->getDriver()->getName(),
      'database name' => $this->db_connection->getDatabase(),
      'database host' => $this->db_connection->getHost(),
      'database user' => $this->db_connection->getUsername(),
    );

    $pass = $this->db_connection->getPassword();
    $parameters['database password'] = empty($pass) ? 'empty' : 'not disclosed';

    return $parameters;
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
}