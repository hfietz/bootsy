<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\SecurityContextAware;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator;

abstract class BaseController implements SecurityContextAware
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
   * @var Validator
   */
  protected $validator;

  /**
   * @var SecurityContextInterface $securityContext
   */

  protected $securityContext;

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

  /**
   * @param \Symfony\Component\Validator\Validator $validator
   */
  public function setValidator($validator)
  {
    $this->validator = $validator;
  }

  /**
   * @param SecurityContextInterface $securityContext
   */
  public function setSecurityContext(SecurityContextInterface $securityContext)
  {
    $this->securityContext = $securityContext;
  }

  /**
   * @return int|null
   */
  protected function getCurrentUserId()
  {
    $user = $this->securityContext->getToken()->getUser();
    if (is_a($user, 'Econemon\Bootsy\UserBundle\Entity\User')) {
      $userId = $user->getId();
    } else {
      $userId = NULL;
    }

    return $userId;
  }
}