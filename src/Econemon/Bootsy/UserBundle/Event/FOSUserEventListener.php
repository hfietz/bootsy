<?php

namespace Econemon\Bootsy\UserBundle\Event;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class FOSUserEventListener implements EventSubscriberInterface
{
  /**
   * @var RouterInterface
   */
  protected $router;

  public function setRedirectToProfileEdit(FormEvent $event)
  {
    $url = $this->router->generate('fos_user_profile_edit');

    $event->setResponse(new RedirectResponse($url));
  }

  /**
   * @inheritdoc
   */
  public static function getSubscribedEvents()
  {
    return array(
      FOSUserEvents::PROFILE_EDIT_SUCCESS => 'setRedirectToProfileEdit',
      FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'setRedirectToProfileEdit', // because we change the password on the profile page
      FOSUserEvents::RESETTING_RESET_SUCCESS => 'setRedirectToProfileEdit',
    );
  }

  /**
   * @param \Symfony\Component\Routing\RouterInterface $router
   */
  public function setRouter($router)
  {
    $this->router = $router;
  }
}