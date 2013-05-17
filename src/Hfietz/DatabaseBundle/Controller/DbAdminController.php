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
    try {
      if (NULL === $this->db_connection) {
        throw new DatabaseException('Missing a connection object, something went wrong with the dependency injection.');
      }

      $variables['dbDriver'] = $this->db_connection->getDriver()->getName();
      $variables['dbName'] = $this->db_connection->getDatabase();
      $variables['dbHost'] = $this->db_connection->getHost();
      $variables['dbUser'] = $this->db_connection->getUsername();
      $pass = $this->db_connection->getPassword();
      $variables['dbPass'] = empty($pass) ? 'empty' : 'not disclosed';

      if (FALSE === $this->db_connection->isConnected()) {
        $this->db_connection->connect(); // This is likely to throw an exception, otherwise we would probably be connected
      }

      if ($this->db_connection->isConnected()) {
        $variables['message'] = 'DB connection is up';
        $variables['messageType'] = 'success';
        $variables['dbStatus'] = 'connected';
      } else {
        throw new DatabaseException('DB is not connected, but no error was thrown during connect.');
      }
    } catch (Exception $e) {
      $variables['message'] = $e->getMessage();
      $variables['messageType'] = 'error';
      $variables['dbStatus'] = 'no connection';
    }

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
}