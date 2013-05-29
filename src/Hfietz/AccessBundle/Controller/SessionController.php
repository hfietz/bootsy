<?php

namespace Hfietz\AccessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SessionController extends Controller
{
  public function loginAction()
  {
    return $this->render('HfietzAccessBundle:Session:login.html.twig', array(
      'pageTitle' => 'Login',
      'labelUsername' => 'Benutzername',
      'labelPassword' => 'Passwort',
      'labelLoginButton' => 'Login',
    ));
  }
}