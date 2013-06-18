<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseController
{
  /**
   * @var RouterInterface
   */
  protected $router;

  /**
   * @var EngineInterface
   */
  protected $templateEngine;

  /**
   * @param \Symfony\Component\Routing\RouterInterface $router
   */
  public function setRouter($router)
  {
    $this->router = $router;
  }

  /**
   * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templateEngine
   */
  public function setTemplateEngine($templateEngine)
  {
    $this->templateEngine = $templateEngine;
  }
}