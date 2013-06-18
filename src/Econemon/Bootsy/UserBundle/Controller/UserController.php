<?php

namespace Econemon\Bootsy\UserBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Controller\BaseController;
use Econemon\Bootsy\ApplicationBundle\Service\MenuExtender;
use Econemon\Bootsy\ApplicationBundle\Service\SecurityContextAware;
use Econemon\Bootsy\UserBundle\Form\ProfileFormType;

use Exception;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserManagerInterface;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserController extends BaseController implements SecurityContextAware, MenuExtender
{
  /**
   * @var UserManagerInterface
   */
  protected $userManager;

  /**
   * @var FactoryInterface
   */
  protected $profileFormFactory;

  /**
   * @var FactoryInterface
   */
  protected $passwordFormFactory;

  /**
   * @var SecurityContextInterface
   */
  protected $securityContext;

  public function editProfileAction(Request $request)
  {
    $user = $this->securityContext->getToken()->getUser();

    $profileForm = $this->profileFormFactory->createForm();
    $profileForm->setData($user);

    $passwordForm = $this->passwordFormFactory->createForm();
    $passwordForm->setData($user);

    if ($request->isMethod('POST')) {
      // password form is just generated here, the processing still happens at the original controller in the FOSUserBundle
      $profileForm->bind($request);
      if ($profileForm->isValid()) {
        try {
          $newEmail = $profileForm->get('newEmail');
          if (!$newEmail->isEmpty() && $newEmail->getData() !== $user->getEmail()) {
            $this->triggerNewEmailConfiguration($user->getId(), $newEmail->getData());
          }
          $this->userManager->updateUser($user);
          $message = $this->translator->trans('form.user.successMessage', array(), 'bootsy_user');
          $type = 'notice';
        } catch (Exception $e) {
          // TODO: The error bundle should handle both flash messages and error logging.
          $message = $this->translator->trans('form.user.unexpectedErrorMessage', array('%error%' => $e->getMessage()), 'bootsy_user');
          $type = 'error';
        }
        $this->session->getFlashBag()->add($type, $message);
        return new RedirectResponse($this->router->generate('econemon_bootsy_user_profile_edit'));
      }
    }

    $vars = array(
      'form_profile' => $profileForm->createView(),
      'form_password' => $passwordForm->createView(),
    );
    return $this->templateEngine->renderResponse('EconemonBootsyUserBundle:Profile:edit.html.twig', $vars);
  }

  /**
   * @param \FOS\UserBundle\Model\UserManagerInterface $userManager
   */
  public function setUserManager($userManager)
  {
    $this->userManager = $userManager;
  }

  /**
   * @param \FOS\UserBundle\Form\Factory\FactoryInterface $formFactory
   */
  public function setProfileFormFactory($formFactory)
  {
    $this->profileFormFactory = $formFactory;
  }

  public function setSecurityContext(SecurityContextInterface $securityContext)
  {
    $this->securityContext = $securityContext;
  }

  /**
   * @param \FOS\UserBundle\Form\Factory\FactoryInterface $passwordFormFactory
   */
  public function setPasswordFormFactory($passwordFormFactory)
  {
    $this->passwordFormFactory = $passwordFormFactory;
  }

  protected function triggerNewEmailConfiguration($id, $newEmail)
  {
    // TODO
    $breakpoint = 'here';
  }

  public function getMenuDescription()
  {
    return array(
      'menu.user._section' => array(
        'menu.user.profile' => 'econemon_bootsy_user_profile_edit',
      ),
    );
  }
}