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
            $user->setRoles(array($data->role));
            $user->setEnabled(FALSE);
            $user->setConfirmationToken($this->tokenGenerator->generateToken());

            // username and password may not be NULL, so we enter something reasonable. The password has to be reset by the user anyway.
            $user->setUsername(substr($data->email, 0, strpos($data->email, '@')));
            $user->setPlainPassword($this->tokenGenerator->generateToken());

            // Because the confirmation link sent to the user will lead to the (modified) password reset page, we need to note this down as a password reset request
            $user->setPasswordRequestedAt(new \DateTime());

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

  public function editProfileAction(Request $request, $id = NULL)
  {
    if (NULL !== $id && $this->securityContext->isGranted('ROLE_ADMIN')) {
      $user = $this->userManager->findUserBy(array('id' => $id));
    } else {
      $user = $this->securityContext->getToken()->getUser();
    }

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
        return new RedirectResponse($this->router->generate('econemon_bootsy_user_profile_edit', array('id' => $user->getId())));
      }
    }

    $vars = array(
      'form_profile' => $profileForm->createView(),
      'form_password' => $passwordForm->createView(),
    );
    return $this->templateEngine->renderResponse('EconemonBootsyUserBundle:Profile:edit.html.twig', $vars);
  }

  public function listAction()
  {
    $users = $this->userManager->findUsers();

    return $this->templateEngine->renderResponse('EconemonBootsyUserBundle:Admin:list.html.twig', array('users' => $users));
  }

  public function deleteUserAction($id)
  {
    try {
      $this->checkIsOwnAccount($id, 'Can\'t delete your own account');

      $user = $this->userManager->findUserBy(array('id' => $id));
      if (NULL !== $user) {
        $this->userManager->deleteUser($user);
      } else {
        throw new Exception('There is no user with the id ' . $id);
      }

      $this->session->getFlashBag()->add('notice', $this->translator->trans('actions.user.delete.success', array('%id%' => $id), self::TRANSLATION_DOMAIN));
    } catch (Exception $e) {
      $this->session->getFlashBag()->add('error', $this->translator->trans('actions.user.delete.error', array('%id%' => $id, '%message%' => $e->getMessage()), self::TRANSLATION_DOMAIN));
    }

    return new RedirectResponse($this->router->generate('econemon_bootsy_user_list'));
  }

  public function setUserEnabledAction($id, $enabled)
  {
    $result = 'actions.user.setEnabled.result.' . ($enabled ? 'unlocked' : 'locked');
    $result = $this->translator->trans($result, array(), self::TRANSLATION_DOMAIN);
    try {
      $this->checkIsOwnAccount($id, 'Can\'t enable / disable your own account');

      $user = $this->userManager->findUserBy(array('id' => $id));
      if (NULL !== $user) {
        $user->setEnabled($enabled);
        $this->userManager->updateUser($user);
      } else {
        throw new Exception('There is no user with the id ' . $id);
      }

      $this->session->getFlashBag()->add('notice', $this->translator->trans('actions.user.setEnabled.success', array('%id%' => $id, '%result%' => $result), self::TRANSLATION_DOMAIN));
    } catch (Exception $e) {
      $this->session->getFlashBag()->add('error', $this->translator->trans('actions.user.setEnabled.error', array('%id%' => $id, '%message%' => $e->getMessage(), '%result%' => $result), self::TRANSLATION_DOMAIN));
    }

    return new RedirectResponse($this->router->generate('econemon_bootsy_user_list'));
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
        'menu.user.list' => 'econemon_bootsy_user_list',
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

  /**
   * @param $id
   * @param $errorMessage
   * @throws \Exception
   */
  protected function checkIsOwnAccount($id, $errorMessage)
  {
    $executingUser = $this->securityContext->getToken()->getUser();
    if (is_a($executingUser, 'FOS\UserBundle\Model\UserInterface') && $id == $executingUser->getId()) {
      throw new Exception($errorMessage);
    }
  }
}