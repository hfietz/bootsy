<?php
namespace Hfietz\DatabaseBundle\Controller;

use Exception;

use Hfietz\DatabaseBundle\Form\Model\ConfigFormData;
use Hfietz\DatabaseBundle\Form\Type\ConfigForm;
use Hfietz\DatabaseBundle\Model\ScriptView;

use Hfietz\DatabaseBundle\Service\DatabaseService;
use Hfietz\DatabaseBundle\Service\DatabaseServiceAware;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;

// TODO: secure those methods / routes
class DbAdminController implements DatabaseServiceAware
{
  /**
   * @var DatabaseService
   */
  protected $databaseService;

  /**
   * @var EngineInterface
   */
  protected $template_engine;

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
    if (FALSE === $this->databaseService->verifyConnection()) {
      return $this->statusAction(); // TODO: Investigate: How will forwarding be handled in 2.3? Are there any issues forwarding like this?
    } else {

      $versions = array();
      foreach ($this->databaseService->loadScripts() as $script) {
        $script->load();
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
    $variables = $this->getStatusView();

    return $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
  }

  /**
   * @return array
   */
  protected function getStatusView($formView = NULL)
  {
    $variables = array(
      'message' => 'Checking DB status...',
      'messageType' => 'message',
      'dbStatus' => 'unknown',
    );

    if (FALSE === $this->databaseService->verifyConnection($variables['message'])) {
      $variables['messageType'] = 'error';
      $variables['dbStatus'] = 'no connection';
    } else {
      $variables['message'] = 'DB connection is up';
      $variables['messageType'] = 'success';
      $variables['dbStatus'] = 'connected';
    }

    $variables['parameters'] = $this->databaseService->loadConfigUsed();

    $variables['configFile'] = $this->databaseService->getConfigFileRelativePath();

    $config = $this->databaseService->loadConfig();

    $variables['config'] = $config;


    if (!is_a($formView, '\Symfony\Component\Form\FormView')) {
      $formData = ConfigFormData::fromConfig($config);
      $options = array();
      $form = $this->formFactory->create(new ConfigForm(), $formData, $options);

      $formView = $form->createView();
    }

    $variables['form'] = $formView;

    return $variables;
  }

  public function configureAction(Request $req)
  {
    $response = new RedirectResponse($this->router->generate('db_status'));

    $formData = new ConfigFormData();

    $form = $this->formFactory->create(new ConfigForm(), $formData);

    if ($req->isMethod('POST')) {
      $form->bind($req);

      if ($form->isValid()) {
        $config = $formData->toConfig();
        $this->databaseService->saveConfig($config);
      } else {
        $variables = $this->getStatusView($form->createView());

        $response = $this->getTemplateEngine()->renderResponse('HfietzDatabaseBundle:DbAdmin:db_status.html.twig', $variables);
      }
    }

    return $response;
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

  /**
   * @param \Hfietz\DatabaseBundle\Service\DatabaseService $databaseService
   */
  public function setDatabaseService(DatabaseService $databaseService = NULL)
  {
    $this->databaseService = $databaseService;
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
}