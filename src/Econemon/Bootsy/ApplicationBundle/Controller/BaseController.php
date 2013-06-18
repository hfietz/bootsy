<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
   * @var Session
   */
  protected $session;

  /**
   * @var TranslatorInterface
   */
  protected $translator;

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

  /**
   * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
   */
  public function setSession($session)
  {
    $this->session = $session;
  }

  /**
   * @param \Symfony\Component\Translation\TranslatorInterface $translator
   */
  public function setTranslator($translator)
  {
    $this->translator = $translator;
  }
}