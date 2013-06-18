<?php

namespace Econemon\Bootsy\UserBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Controller\BaseController;
use Econemon\Bootsy\ApplicationBundle\Service\MenuExtender;
use Econemon\Bootsy\ApplicationBundle\Service\SecurityContextAware;
use Econemon\Bootsy\UserBundle\Form\Model\NewUserData;
use Econemon\Bootsy\UserBundle\Form\ProfileFormType;

use Exception;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;

use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class UserController extends BaseController implements SecurityContextAware, MenuExtender
{
  const TRANSLATION_DOMAIN = 'bootsy_user';
  /**
   * @var UserManagerInterface
   */
  protected $userManager;

  /**
   * @var MailerInterface
   */
  protected $fosUserMailer;

  /**
   * @var TokenGeneratorInterface
   */
  protected $tokenGenerator;

  /**
   * @var FactoryInterface
   */
  protected $profileFormFactory;

  /**
   * @var FactoryInterface
   */
  protected $passwordFormFactory;

  /**
   * @var FormFactoryInterface
   */
  protected $formFactory;

  /**
   * @var SecurityContextInterface
   */
  protected $securityContext;

  public function newUserAction(Request $request)
  {
    $data = new NewUserData();

    $form = $this->formFactory->create('econemon_bootsy_user_new_form', $data);

    if ($request->isMethod('POST')) {
      $form->bind($request);

      if ($form->isValid()) {
        $existingUser = $this->userManager->findUserByEmail($data->email);
        if (NULL === $existingUser) {
          try {
            $user = $this->userManager->createUser();

            $user->setEmail($data->email);
            $user->setUsername(substr($data->email, 0, strpos($data->email, '@')));
            $user->setPlainPassword($this->tokenGenerator->generateToken());
            $user->setRoles(array($data->role));
            $user->setEnabled(FALSE);
            $user->setConfirmationToken($this->tokenGenerator->generateToken());

            $this->userManager->updateUser($user);

            $this->fosUserMailer->sendConfirmationEmailMessage($user);

            $message = $this->translator->trans('registration.form.messages.success', array('%address%' => $data->email), self::TRANSLATION_DOMAIN);
            $this->session->getFlashBag()->add('notice', $message);
          } catch (Exception $e) {
            $message = $this->translator->trans('registration.form.messages.error', array('%message%' => $e->getMessage()), self::TRANSLATION_DOMAIN);
            $this->session->getFlashBag()->add('error', $message);
          }
        } else {
          $message = $this->translator->trans('registration.form.messages.emailExists', array('%address%' => $data->email), self::TRANSLATION_DOMAIN);
          $this->session->getFlashBag()->add('error', $message);
        }
      }
    }

    return $this->templateEngine->renderResponse('EconemonBootsyUserBundle:Admin:new_user.html.twig', array('form' => $form->createView()));
  }

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
          $message = $this->translator->trans('form.user.successMessage', array(), self::TRANSLATION_DOMAIN);
          $type = 'notice';
        } catch (Exception $e) {
          // TODO: The error bundle should handle both flash messages and error logging.
          $message = $this->translator->trans('form.user.unexpectedErrorMessage', array('%error%' => $e->getMessage()), self::TRANSLATION_DOMAIN);
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
        'menu.user.new' => 'econemon_bootsy_user_new',
      ),
    );
  }

  /**
   * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
   */
  public function setFormFactory($formFactory)
  {
    $this->formFactory = $formFactory;
  }

  /**
   * @param \FOS\UserBundle\Mailer\MailerInterface $fosUserMailer
   */
  public function setFosUserMailer($fosUserMailer)
  {
    $this->fosUserMailer = $fosUserMailer;
  }

  /**
   * @param \FOS\UserBundle\Util\TokenGeneratorInterface $tokenGenerator
   */
  public function setTokenGenerator($tokenGenerator)
  {
    $this->tokenGenerator = $tokenGenerator;
  }
}